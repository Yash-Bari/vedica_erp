<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Models\SalaryPayment;
use App\Models\SalaryReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Services\SalaryReceiptService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryController extends Controller
{
    /**
     * Display the salary management index page.
     */
    public function index()
    {
        $this->authorize('viewAny', SalaryStructure::class);

        // Base query for employees
        $query = Employee::with(['activeSalaryStructure']);

        // Apply status filter
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Apply role filter
        if (request()->filled('role')) {
            $query->where('role', request('role'));
        }

        // Paginate employees
        $employees = $query->paginate(15);

        // Get summary statistics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $employeesWithSalaryStructure = Employee::whereHas('activeSalaryStructure')->count();
        
        // Get current month's payment statistics
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $pendingPayments = Employee::where('status', 'active')
            ->whereDoesntHave('salaryPayments', function($query) use ($currentMonth, $currentYear) {
                $query->where('year', $currentYear)
                      ->where('month', $currentMonth);
            })->count();

        $processedPayments = SalaryPayment::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->count();

        // Calculate pending payroll amount
        $pendingPayrollAmount = Employee::where('status', 'active')
            ->whereDoesntHave('salaryPayments', function($query) use ($currentMonth, $currentYear) {
                $query->where('year', $currentYear)
                      ->where('month', $currentMonth);
            })
            ->whereHas('activeSalaryStructure')
            ->with('activeSalaryStructure')
            ->get()
            ->sum(function($employee) {
                return $employee->activeSalaryStructure->calculateNetSalaryFromJson();
            });
        
        // Calculate total monthly payroll
        $totalMonthlyPayroll = SalaryStructure::where('is_active', true)
            ->get()
            ->sum(function($structure) {
                return $structure->calculateNetSalaryFromJson();
            });

        // Get unique roles for filter dropdown
        $roles = Employee::distinct()->pluck('role');

        // Get unique statuses for filter dropdown
        $statuses = ['active', 'inactive', 'on_leave'];

        return view('salaries.index', compact(
            'employees', 
            'totalEmployees',
            'activeEmployees', 
            'employeesWithSalaryStructure', 
            'pendingPayments',
            'processedPayments',
            'pendingPayrollAmount',
            'totalMonthlyPayroll', 
            'roles',
            'statuses'
        ));
    }

    /**
     * Display a listing of salary payments.
     */
    public function paymentsIndex()
    {
        $payments = SalaryPayment::with(['employee', 'receipt'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalPayments = SalaryPayment::count();
        $currentMonthPayments = SalaryPayment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $pendingPayments = SalaryPayment::where('status', 'Pending')->count();
        $totalAmount = SalaryPayment::where('status', 'Paid')->sum('net_salary');

        return view('salaries.payments.index', compact(
            'payments',
            'totalPayments',
            'currentMonthPayments',
            'pendingPayments',
            'totalAmount'
        ));
    }

    /**
     * Display a listing of salary structures.
     */
    public function structureIndex()
    {
        $this->authorize('viewAny', SalaryStructure::class);

        $structures = SalaryStructure::with(['employee'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('salaries.structures.index', compact('structures'));
    }

    /**
     * Show form to create a new salary structure
     */
    public function structureCreate(Request $request)
    {
        $this->authorize('create', SalaryStructure::class);

        $employee = null;
        if ($request->has('employee_id')) {
            $employee = Employee::findOrFail($request->employee_id);
        }

        // Get all active employees who don't have an active salary structure
        $employees = Employee::where('status', 'active')
            ->whereDoesntHave('salaryStructures', function($query) {
                $query->where('is_active', true);
            })
            ->get();

        return view('salaries.structure.create', compact('employee', 'employees'));
    }

    /**
     * Show list of all salary payments
     */
    public function paymentsIndexList()
    {
        $this->authorize('viewAny', SalaryPayment::class);

        $payments = SalaryPayment::with(['employee', 'salaryStructure'])
            ->latest()
            ->paginate(15);

        return view('salaries.payments.index', compact('payments'));
    }

    /**
     * Show the form for processing a salary payment.
     */
    public function processPayment(Employee $employee)
    {
        $this->authorize('process', SalaryPayment::class);

        if (!$employee->activeSalaryStructure) {
            return redirect()->route('salaries.index')
                ->withErrors(['error' => 'Employee does not have an active salary structure.']);
        }

        // Check if salary is already processed for current month
        $existingPayment = SalaryPayment::where('employee_id', $employee->id)
            ->where('month', now()->format('F'))
            ->where('year', now()->year)
            ->first();

        if ($existingPayment) {
            return redirect()->route('salaries.payments.show', $existingPayment)
                ->withErrors(['error' => 'Salary payment already exists for this month.']);
        }

        return view('salaries.payments.process', compact('employee'));
    }

    /**
     * Store a new salary payment.
     */
    public function storePayment(Request $request, Employee $employee)
    {
        $this->authorize('process', SalaryPayment::class);

        $validated = $request->validate([
            'month' => 'required|string',
            'year' => 'required|integer',
            'days_worked' => 'required|integer|min:0|max:31',
            'overtime_hours' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
            'allowances' => 'required|numeric|min:0',
            'tax_deduction' => 'required|numeric|min:0',
            'other_deductions' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Bank Transfer,Cash,Cheque'
        ]);

        try {
            DB::beginTransaction();

            // Calculate salary components
            $structure = $employee->activeSalaryStructure;
            $dailyRate = $structure->base_salary / 22; // Standard working days
            $basePay = $dailyRate * $validated['days_worked'];
            $overtimePay = $validated['overtime_hours'] * $structure->hourly_rate * 1.5;
            
            $totalEarnings = $basePay + $overtimePay + $validated['bonus'] + $validated['allowances'];
            $totalDeductions = $validated['tax_deduction'] + $validated['other_deductions'];
            $netSalary = $totalEarnings - $totalDeductions;

            // Create salary payment
            $payment = SalaryPayment::create([
                'employee_id' => $employee->id,
                'month' => $validated['month'],
                'year' => $validated['year'],
                'base_salary' => $structure->base_salary,
                'days_worked' => $validated['days_worked'],
                'overtime_hours' => $validated['overtime_hours'],
                'overtime_rate' => $structure->hourly_rate * 1.5,
                'overtime_pay' => $overtimePay,
                'bonus' => $validated['bonus'],
                'additional_allowances' => $validated['allowances'],
                'tax_deduction' => $validated['tax_deduction'],
                'other_deductions' => $validated['other_deductions'],
                'total_earnings' => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'payment_method' => $validated['payment_method'],
                'payment_date' => now(),
                'status' => 'Paid'
            ]);

            // Generate receipt
            $receiptService = app(SalaryReceiptService::class);
            $receipt = $receiptService->generateReceipt($payment);

            DB::commit();

            return redirect()->route('salaries.payments.show', $payment)
                ->with('success', 'Salary payment processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to process salary payment: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to process salary payment. Please try again.']);
        }
    }

    /**
     * Process bulk salary payments.
     */
    public function processBulkPayments()
    {
        $this->authorize('processBulk', SalaryPayment::class);

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get all active employees with pending payments
        $employees = Employee::where('status', 'active')
            ->whereDoesntHave('salaryPayments', function($query) use ($currentMonth, $currentYear) {
                $query->where('year', $currentYear)
                      ->where('month', $currentMonth);
            })
            ->whereHas('activeSalaryStructure')
            ->with('activeSalaryStructure')
            ->get();

        $processed = 0;
        DB::beginTransaction();
        
        try {
            foreach ($employees as $employee) {
                $structure = $employee->activeSalaryStructure;
                
                // Create payment record
                $payment = new SalaryPayment([
                    'employee_id' => $employee->id,
                    'salary_structure_id' => $structure->id,
                    'year' => $currentYear,
                    'month' => $currentMonth,
                    'basic_salary' => $structure->base_salary,
                    'allowances' => $structure->allowances,
                    'deductions' => $structure->deductions,
                    'net_salary' => $structure->calculateNetSalaryFromJson(),
                    'payment_date' => now(),
                    'status' => 'processing'
                ]);

                $payment->save();

                // Generate receipt
                $receipt = new SalaryReceipt([
                    'salary_payment_id' => $payment->id,
                    'generated_at' => now(),
                    'status' => 'generated'
                ]);

                $receipt->save();
                $processed++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully processed payments for {$processed} employees.",
                'processed' => $processed
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to process bulk payments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payments. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a salary payment.
     */
    public function showPayment(SalaryPayment $payment)
    {
        $this->authorize('view', $payment);
        
        $payment->load(['employee', 'receipt']);
        
        return view('salaries.payments.show', compact('payment'));
    }

    /**
     * Process salary payment for an employee
     */
    public function process(Employee $employee)
    {
        // Check if employee has an active salary structure
        $structure = $employee->activeSalaryStructure;
        if (!$structure) {
            return back()->with('error', 'No active salary structure found for this employee.');
        }

        // Check if payment for current month already exists
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $paymentExists = SalaryPayment::where('employee_id', $employee->id)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->exists();

        if ($paymentExists) {
            return back()->with('error', 'Salary payment for current month already exists.');
        }

        try {
            DB::beginTransaction();

            // Calculate salary components
            $basicSalary = $structure->base_salary ?? 0;
            $allowances = json_decode($structure->allowances, true) ?? [];
            $deductions = json_decode($structure->deductions, true) ?? [];
            $netSalary = $basicSalary + array_sum($allowances) - array_sum($deductions);

            // Create payment record
            $payment = new SalaryPayment([
                'employee_id' => $employee->id,
                'salary_structure_id' => $structure->id,
                'year' => $currentYear,
                'month' => $currentMonth,
                'basic_salary' => $basicSalary,
                'allowances' => json_encode($allowances),
                'deductions' => json_encode($deductions),
                'net_salary' => $netSalary,
                'payment_date' => now(),
                'status' => 'processing'
            ]);

            $payment->save();

            // Generate receipt
            $receipt = new SalaryReceipt([
                'salary_payment_id' => $payment->id,
                'receipt_number' => SalaryReceipt::generateReceiptNumber(),
                'generated_at' => now(),
                'status' => 'generated',
                'generated_by' => auth()->id()
            ]);

            $receipt->save();

            DB::commit();

            return redirect()
                ->route('salaries.payments.show', $payment)
                ->with('success', 'Salary payment processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing salary payment: ' . $e->getMessage());

            return back()->with('error', 'Error processing salary payment. Please try again.');
        }
    }

    /**
     * Display the specified salary payment.
     */
    public function show(SalaryPayment $payment)
    {
        $this->authorize('view', $payment);
        
        return view('salaries.payments.show', compact('payment'));
    }

    /**
     * Store a newly created salary structure
     */
    public function structureStore(Request $request)
    {
        $this->authorize('create', SalaryStructure::class);

        try {
            DB::beginTransaction();

            // Validate request
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'base_salary' => 'required|numeric|min:0',
                'hourly_rate' => 'required|numeric|min:0',
                'overtime_rate' => 'required|numeric|min:0',
                'bonus_percentage' => 'required|numeric|min:0',
                'house_rent' => 'nullable|numeric|min:0',
                'conveyance' => 'nullable|numeric|min:0',
                'medical' => 'nullable|numeric|min:0',
                'performance_bonus' => 'nullable|numeric|min:0',
                'provident_fund' => 'nullable|numeric|min:0',
                'professional_tax' => 'nullable|numeric|min:0',
                'other' => 'nullable|numeric|min:0'
            ]);

            // Deactivate any existing active salary structure
            SalaryStructure::where('employee_id', $validated['employee_id'])
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Format allowances and deductions as JSON
            $allowances = [
                'house_rent' => $request->house_rent,
                'conveyance' => $request->conveyance,
                'medical' => $request->medical,
                'performance_bonus' => $request->performance_bonus
            ];

            $deductions = [
                'provident_fund' => $request->provident_fund,
                'professional_tax' => $request->professional_tax,
                'other' => $request->other
            ];

            // Create new salary structure
            $salaryStructure = SalaryStructure::create([
                'employee_id' => $validated['employee_id'],
                'base_salary' => $validated['base_salary'],
                'hourly_rate' => $validated['hourly_rate'],
                'overtime_rate' => $validated['overtime_rate'],
                'bonus_percentage' => $validated['bonus_percentage'],
                'allowances' => json_encode($allowances),
                'deductions' => json_encode($deductions),
                'is_active' => true,
                'effective_date' => now()->toDateString()
            ]);

            DB::commit();

            return redirect()
                ->route('salaries.structure.show', $salaryStructure)
                ->with('success', 'Salary structure created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating salary structure: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error creating salary structure. Please try again.');
        }
    }

    /**
     * Show a salary structure's details
     */
    public function structureShow(SalaryStructure $structure)
    {
        $this->authorize('view', $structure);

        $employee = $structure->employee;
        $allowances = json_decode($structure->allowances, true);
        $deductions = json_decode($structure->deductions, true);

        $totalAllowances = array_sum($allowances);
        $totalDeductions = array_sum($deductions);
        $netSalary = $structure->base_salary + $totalAllowances - $totalDeductions;

        return view('salaries.structure.show', compact(
            'structure',
            'employee',
            'allowances',
            'deductions',
            'totalAllowances',
            'totalDeductions',
            'netSalary'
        ));
    }

    /**
     * Show form to edit salary structure
     */
    public function structureEdit(SalaryStructure $structure)
    {
        $this->authorize('update', $structure);

        $employee = $structure->employee;
        $allowances = json_decode($structure->allowances, true);
        $deductions = json_decode($structure->deductions, true);

        $totalAllowances = array_sum($allowances);
        $totalDeductions = array_sum($deductions);
        $totalEarnings = $structure->base_salary + $totalAllowances;
        $netSalary = $totalEarnings - $totalDeductions;

        return view('salaries.structure.edit', compact(
            'structure',
            'employee',
            'allowances',
            'deductions',
            'totalAllowances',
            'totalDeductions',
            'totalEarnings',
            'netSalary'
        ));
    }

    /**
     * Update salary structure
     */
    public function structureUpdate(Request $request, SalaryStructure $structure)
    {
        $this->authorize('update', $structure);

        try {
            DB::beginTransaction();

            // Validate request
            $validated = $request->validate([
                'base_salary' => 'required|numeric|min:0',
                'hourly_rate' => 'required|numeric|min:0',
                'overtime_rate' => 'required|numeric|min:0',
                'bonus_percentage' => 'required|numeric|min:0',
                'house_rent' => 'nullable|numeric|min:0',
                'conveyance' => 'nullable|numeric|min:0',
                'medical' => 'nullable|numeric|min:0',
                'performance_bonus' => 'nullable|numeric|min:0',
                'provident_fund' => 'nullable|numeric|min:0',
                'professional_tax' => 'nullable|numeric|min:0',
                'other' => 'nullable|numeric|min:0'
            ]);

            // Format allowances and deductions as JSON
            $allowances = [
                'house_rent' => $request->house_rent,
                'conveyance' => $request->conveyance,
                'medical' => $request->medical,
                'performance_bonus' => $request->performance_bonus
            ];

            $deductions = [
                'provident_fund' => $request->provident_fund,
                'professional_tax' => $request->professional_tax,
                'other' => $request->other
            ];

            // Update salary structure
            $structure->update([
                'base_salary' => $validated['base_salary'],
                'hourly_rate' => $validated['hourly_rate'],
                'overtime_rate' => $validated['overtime_rate'],
                'bonus_percentage' => $validated['bonus_percentage'],
                'allowances' => json_encode($allowances),
                'deductions' => json_encode($deductions)
            ]);

            DB::commit();

            return redirect()
                ->route('salaries.structure.show', $structure)
                ->with('success', 'Salary structure updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating salary structure: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Error updating salary structure. Please try again.');
        }
    }

    /**
     * Download salary receipt
     */
    public function downloadReceipt(SalaryReceipt $receipt)
    {
        $this->authorize('view', $receipt->salaryPayment);

        $payment = $receipt->salaryPayment;
        $employee = $payment->employee;

        // Return view for now, we'll implement PDF download later
        return view('salaries.receipts.show', [
            'receipt' => $receipt,
            'payment' => $payment,
            'employee' => $employee
        ]);
    }

    /**
     * Generate PDF for salary receipt
     */
    private function generateReceiptPDF(SalaryReceipt $receipt)
    {
        $payment = $receipt->salaryPayment;
        $employee = $payment->employee;

        $pdf = PDF::loadView('salaries.receipts.pdf', [
            'receipt' => $receipt,
            'payment' => $payment,
            'employee' => $employee
        ]);

        return $pdf;
    }
}
