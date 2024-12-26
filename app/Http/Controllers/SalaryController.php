<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\SalaryStructure;
use App\Models\SalaryReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        // Authorize view of salary management
        $this->authorize('viewAny', SalaryStructure::class);

        // Base query for employees
        $query = Employee::with(['activeSalaryStructure']);

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Paginate employees
        $employees = $query->paginate(15);

        // Get summary statistics
        $activeEmployees = Employee::where('status', 'active')->count();
        $employeesWithSalaryStructure = Employee::whereHas('activeSalaryStructure')->count();
        
        // Calculate total monthly payroll using the method from SalaryStructure model
        $totalMonthlyPayroll = SalaryStructure::where('is_active', true)
            ->get()
            ->sum(function($salaryStructure) {
                return $salaryStructure->calculateNetSalary();
            });

        // Get unique roles
        $roles = Employee::distinct()->pluck('role');

        return view('salaries.index', compact(
            'employees', 
            'activeEmployees', 
            'employeesWithSalaryStructure', 
            'totalMonthlyPayroll', 
            'roles'
        ));
    }

    public function processSalary(Request $request)
    {
        // Authorize processing salaries
        $this->authorize('processSalaries', SalaryPayment::class);

        $validatedData = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|in:January,February,March,April,May,June,July,August,September,October,November,December'
        ]);

        try {
            DB::beginTransaction();

            // Process salaries for all active employees
            $result = SalaryPayment::processMonthlySalaries(
                $validatedData['year'], 
                $validatedData['month']
            );

            DB::commit();

            return redirect()->route('salaries.index')
                ->with('success', "Salaries processed for {$validatedData['month']} {$validatedData['year']}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to process salaries: ' . $e->getMessage()]);
        }
    }

    public function markAsPaid(Request $request, $id)
    {
        // Authorize marking salary as paid
        $this->authorize('markAsPaid', SalaryPayment::class);

        $validatedData = $request->validate([
            'payment_method' => 'required|in:Cash,Bank Transfer,Cheque'
        ]);

        try {
            $salaryPayment = SalaryPayment::findOrFail($id);

            // Mark salary as paid and generate receipt
            $receipt = $salaryPayment->markAsPaid($validatedData['payment_method']);

            return redirect()->route('salaries.receipts.show', $receipt->id)
                ->with('success', 'Salary marked as paid. Receipt generated.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to mark salary as paid: ' . $e->getMessage()]);
        }
    }

    public function showReceipt(SalaryReceipt $salaryReceipt)
    {
        // Authorize view of salary receipt
        $this->authorize('view', $salaryReceipt);

        // Load related models
        $salaryReceipt->load(['salaryPayment', 'employee']);

        return view('salaries.receipt', compact('salaryReceipt'));
    }

    public function createSalaryStructure(Request $request)
    {
        // Authorize creating salary structure
        $this->authorize('create', SalaryStructure::class);

        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'base_salary' => 'required|numeric|min:0',
            'house_rent_allowance' => 'nullable|numeric|min:0',
            'conveyance_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'provident_fund' => 'nullable|numeric|min:0',
            'professional_tax' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0'
        ]);

        try {
            // Deactivate previous active structures
            SalaryStructure::where('employee_id', $validatedData['employee_id'])
                ->update(['is_active' => false]);

            // Create new salary structure
            $salaryStructure = SalaryStructure::create([
                'employee_id' => $validatedData['employee_id'],
                'base_salary' => $validatedData['base_salary'],
                'house_rent_allowance' => $validatedData['house_rent_allowance'] ?? 0,
                'conveyance_allowance' => $validatedData['conveyance_allowance'] ?? 0,
                'medical_allowance' => $validatedData['medical_allowance'] ?? 0,
                'performance_bonus' => $validatedData['performance_bonus'] ?? 0,
                'provident_fund' => $validatedData['provident_fund'] ?? 0,
                'professional_tax' => $validatedData['professional_tax'] ?? 0,
                'other_deductions' => $validatedData['other_deductions'] ?? 0,
                'net_salary_percentage' => 100,
                'is_active' => true
            ]);

            return redirect()->route('salaries.structure.index')
                ->with('success', 'Salary structure created successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create salary structure: ' . $e->getMessage()]);
        }
    }

    public function listSalaryStructures(Request $request)
    {
        // Authorize viewing salary structures
        $this->authorize('viewAny', SalaryStructure::class);

        $query = SalaryStructure::with('employee')
            ->where('is_active', true);

        // Optional: Add filtering logic if needed
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $salaryStructures = $query->paginate(15);

        return view('salaries.structure.index', compact('salaryStructures'));
    }

    public function createSalaryStructureForm()
    {
        // Authorize creating salary structure
        $this->authorize('create', SalaryStructure::class);

        $employees = Employee::where('status', 'active')->get();

        return view('salaries.structure.create', compact('employees'));
    }

    public function showSalaryStructure(SalaryStructure $salaryStructure)
    {
        // Authorize viewing specific salary structure
        $this->authorize('view', $salaryStructure);

        return view('salaries.structure.show', compact('salaryStructure'));
    }

    public function editSalaryStructure(SalaryStructure $salaryStructure)
    {
        // Authorize editing salary structure
        $this->authorize('update', $salaryStructure);

        $employees = Employee::where('status', 'active')->get();

        return view('salaries.structure.edit', compact('salaryStructure', 'employees'));
    }

    public function updateSalaryStructure(Request $request, SalaryStructure $salaryStructure)
    {
        // Authorize updating salary structure
        $this->authorize('update', $salaryStructure);

        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'base_salary' => 'required|numeric|min:0',
            'house_rent_allowance' => 'nullable|numeric|min:0',
            'conveyance_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'provident_fund' => 'nullable|numeric|min:0',
            'professional_tax' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0'
        ]);

        try {
            // Deactivate previous active structures for the employee
            SalaryStructure::where('employee_id', $validatedData['employee_id'])
                ->update(['is_active' => false]);

            // Update salary structure
            $salaryStructure->update([
                'employee_id' => $validatedData['employee_id'],
                'base_salary' => $validatedData['base_salary'],
                'house_rent_allowance' => $validatedData['house_rent_allowance'] ?? 0,
                'conveyance_allowance' => $validatedData['conveyance_allowance'] ?? 0,
                'medical_allowance' => $validatedData['medical_allowance'] ?? 0,
                'performance_bonus' => $validatedData['performance_bonus'] ?? 0,
                'provident_fund' => $validatedData['provident_fund'] ?? 0,
                'professional_tax' => $validatedData['professional_tax'] ?? 0,
                'other_deductions' => $validatedData['other_deductions'] ?? 0,
                'net_salary_percentage' => 100,
                'is_active' => true
            ]);

            return redirect()->route('salaries.structure.index')
                ->with('success', 'Salary structure updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update salary structure: ' . $e->getMessage()]);
        }
    }

    public function destroySalaryStructure(SalaryStructure $salaryStructure)
    {
        // Authorize deleting salary structure
        $this->authorize('delete', $salaryStructure);

        try {
            $salaryStructure->delete();

            return redirect()->route('salaries.structure.index')
                ->with('success', 'Salary structure deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete salary structure: ' . $e->getMessage()]);
        }
    }

    public function processIndividualSalary(Employee $employee)
    {
        // Check if salary has already been processed for this month
        $currentMonth = Carbon::now()->format('F');
        $currentYear = Carbon::now()->year;

        $existingSalaryPayment = SalaryPayment::where('employee_id', $employee->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        // If salary has already been processed, redirect to existing receipt
        if ($existingSalaryPayment && $existingSalaryPayment->salaryReceipt) {
            return redirect()->route('salaries.receipt.show', $existingSalaryPayment->salaryReceipt->id)
                ->with('info', "Salary for {$employee->full_name} has already been processed this month.");
        }

        // Proceed with salary processing form
        $salaryPayment = new SalaryPayment([
            'employee_id' => $employee->id,
            'month' => $currentMonth,
            'year' => $currentYear,
        ]);

        return view('salaries.process-form', compact('salaryPayment'));
    }

    public function processForm($salaryPaymentId)
    {
        // Authorize processing salaries
        $this->authorize('processSalaries', SalaryPayment::class);

        $salaryPayment = SalaryPayment::with('employee')->findOrFail($salaryPaymentId);

        // Payment methods
        $paymentMethods = [
            'Cash' => 'Cash',
            'Bank Transfer' => 'Bank Transfer',
            'Cheque' => 'Cheque'
        ];

        return view('salaries.process-form', compact('salaryPayment', 'paymentMethods'));
    }

    public function storeSalaryPayment(Request $request, Employee $employee)
    {
        // Authorize processing individual salary
        $this->authorize('processSalaries', SalaryPayment::class);

        // Validate input
        $validatedData = $request->validate([
            'payment_method' => 'required|in:Cash,Bank Transfer,Cheque',
            'payment_date' => 'required|date',
            'remarks' => 'nullable|string|max:500'
        ]);

        // Check if employee has an active salary structure
        $salaryStructure = $employee->activeSalaryStructure;

        if (!$salaryStructure) {
            return redirect()->route('salaries.index')
                ->withErrors(['error' => 'No active salary structure found for this employee.']);
        }

        // Check if salary has already been processed for this month
        $currentMonth = Carbon::now()->format('F');
        $currentYear = Carbon::now()->year;

        $existingSalaryPayment = SalaryPayment::where('employee_id', $employee->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        if ($existingSalaryPayment && $existingSalaryPayment->salaryReceipt) {
            return redirect()->route('salaries.receipt.show', $existingSalaryPayment->salaryReceipt->id)
                ->with('info', "Salary for {$employee->full_name} has already been processed this month.");
        }

        try {
            DB::beginTransaction();

            // Calculate salary components
            $basicSalary = $salaryStructure->base_salary;
            $houseRentAllowance = $salaryStructure->house_rent_allowance ?? 0;
            $conveyanceAllowance = $salaryStructure->conveyance_allowance ?? 0;
            $medicalAllowance = $salaryStructure->medical_allowance ?? 0;
            $performanceBonus = $salaryStructure->performance_bonus ?? 0;

            $providentFund = $salaryStructure->provident_fund ?? 0;
            $professionalTax = $salaryStructure->professional_tax ?? 0;
            $otherDeductions = $salaryStructure->other_deductions ?? 0;

            $totalEarnings = $basicSalary + $houseRentAllowance + $conveyanceAllowance + 
                             $medicalAllowance + $performanceBonus;
            $totalDeductions = $providentFund + $professionalTax + $otherDeductions;
            $netSalary = $totalEarnings - $totalDeductions;

            // Create salary payment
            $salaryPayment = SalaryPayment::create([
                'employee_id' => $employee->id,
                'month' => $currentMonth,
                'year' => $currentYear,
                'basic_salary' => $basicSalary,
                'house_rent_allowance' => $houseRentAllowance,
                'conveyance_allowance' => $conveyanceAllowance,
                'medical_allowance' => $medicalAllowance,
                'performance_bonus' => $performanceBonus,
                'provident_fund' => $providentFund,
                'professional_tax' => $professionalTax,
                'other_deductions' => $otherDeductions,
                'allowances' => $houseRentAllowance + $conveyanceAllowance + $medicalAllowance + $performanceBonus,
                'deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'payment_method' => $validatedData['payment_method'],
                'payment_date' => $validatedData['payment_date'],
                'status' => 'Paid',
                'remarks' => $validatedData['remarks'] ?? null
            ]);

            // Generate unique receipt number
            $receiptNumber = 'SR-' . now()->format('Ym') . '-' . str_pad($salaryPayment->id, 5, '0', STR_PAD_LEFT);

            // Create salary receipt
            $salaryReceipt = SalaryReceipt::create([
                'salary_payment_id' => $salaryPayment->id,
                'employee_id' => $employee->id,
                'receipt_number' => $receiptNumber,
                'total_earnings' => $netSalary,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'payment_date' => $validatedData['payment_date'],
                'payment_method' => $validatedData['payment_method'],
                'remarks' => $validatedData['remarks'] ?? null
            ]);

            DB::commit();

            // Redirect to receipt view
            return redirect()->route('salaries.receipt.show', $salaryReceipt->id)
                ->with('success', "Salary processed for {$employee->full_name}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to process salary: ' . $e->getMessage()]);
        }
    }

    public function storeSalaryPaymentOld(Request $request, $salaryPaymentId)
    {
        // Authorize processing salaries
        $this->authorize('processSalaries', SalaryPayment::class);

        $validatedData = $request->validate([
            'payment_method' => 'required|in:Cash,Bank Transfer,Cheque',
            'payment_date' => 'required|date',
            'remarks' => 'nullable|string|max:500'
        ]);

        try {
            $salaryPayment = SalaryPayment::findOrFail($salaryPaymentId);

            // Update payment details
            $salaryPayment->payment_method = $validatedData['payment_method'];
            $salaryPayment->payment_date = $validatedData['payment_date'];
            $salaryPayment->remarks = $validatedData['remarks'] ?? null;
            $salaryPayment->status = 'Paid';
            $salaryPayment->save();

            // Generate receipt
            $receipt = SalaryReceipt::create([
                'salary_payment_id' => $salaryPayment->id,
                'employee_id' => $salaryPayment->employee_id,
                'amount' => $salaryPayment->net_salary,
                'payment_method' => $salaryPayment->payment_method,
                'payment_date' => $salaryPayment->payment_date
            ]);

            return redirect()->route('salaries.index')
                ->with('success', "Salary for {$salaryPayment->employee->full_name} processed and paid successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to store salary payment: ' . $e->getMessage()]);
        }
    }

    // Salary Structure Methods
    public function structureIndex()
    {
        $this->authorize('viewAny', SalaryStructure::class);
        return $this->listSalaryStructures(request());
    }

    public function structureCreate()
    {
        $this->authorize('create', SalaryStructure::class);
        return $this->createSalaryStructureForm();
    }

    public function structureStore(Request $request)
    {
        $this->authorize('create', SalaryStructure::class);
        return $this->createSalaryStructure($request);
    }

    public function structureEdit(SalaryStructure $structure)
    {
        $this->authorize('update', $structure);
        $employees = Employee::where('status', 'active')->get();
        return view('salaries.structure.edit', compact('structure', 'employees'));
    }

    public function structureUpdate(Request $request, SalaryStructure $structure)
    {
        $this->authorize('update', $structure);
        
        $validatedData = $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'house_rent_allowance' => 'nullable|numeric|min:0',
            'conveyance_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'provident_fund' => 'nullable|numeric|min:0',
            'professional_tax' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0'
        ]);

        try {
            $structure->update([
                'base_salary' => $validatedData['base_salary'],
                'house_rent_allowance' => $validatedData['house_rent_allowance'] ?? 0,
                'conveyance_allowance' => $validatedData['conveyance_allowance'] ?? 0,
                'medical_allowance' => $validatedData['medical_allowance'] ?? 0,
                'performance_bonus' => $validatedData['performance_bonus'] ?? 0,
                'provident_fund' => $validatedData['provident_fund'] ?? 0,
                'professional_tax' => $validatedData['professional_tax'] ?? 0,
                'other_deductions' => $validatedData['other_deductions'] ?? 0
            ]);

            return redirect()->route('salaries.structure.index')
                ->with('success', 'Salary structure updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update salary structure: ' . $e->getMessage()]);
        }
    }

    public function structureDestroy(SalaryStructure $structure)
    {
        $this->authorize('delete', $structure);

        try {
            $structure->update(['is_active' => false]);
            return redirect()->route('salaries.structure.index')
                ->with('success', 'Salary structure deactivated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to deactivate salary structure: ' . $e->getMessage()]);
        }
    }

    public function report()
    {
        // Authorize viewing salary reports
        $this->authorize('viewAny', SalaryStructure::class);

        // Get date range
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        $startMonth = $startDate->format('F');
        $endMonth = $endDate->format('F');
        $startYear = $startDate->year;
        $endYear = $endDate->year;

        // Get salary payments for the current month
        $salaryPayments = SalaryPayment::with(['employee'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Calculate total payroll
        $totalPayroll = $salaryPayments->sum('net_salary');
        $totalEmployeesPaid = $salaryPayments->count();

        // Get role-wise salary statistics
        $roleSalaries = DB::table('salary_payments')
            ->join('employees', 'salary_payments.employee_id', '=', 'employees.id')
            ->whereBetween('salary_payments.created_at', [$startDate, $endDate])
            ->select(
                'employees.role',
                DB::raw('SUM(salary_payments.net_salary) as total_salary'),
                DB::raw('AVG(salary_payments.net_salary) as average_salary'),
                DB::raw('COUNT(DISTINCT employees.id) as employee_count')
            )
            ->groupBy('employees.role')
            ->get();

        // Get detailed salary information
        $salaryDetails = DB::table('salary_payments')
            ->join('employees', 'salary_payments.employee_id', '=', 'employees.id')
            ->join('salary_structures', function($join) {
                $join->on('employees.id', '=', 'salary_structures.employee_id')
                    ->where('salary_structures.is_active', '=', true);
            })
            ->whereBetween('salary_payments.created_at', [$startDate, $endDate])
            ->select(
                DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as employee_name"),
                'employees.role as employee_role',
                'salary_structures.base_salary',
                DB::raw('salary_payments.allowances as overtime_pay'),
                'salary_payments.deductions as total_deductions',
                'salary_payments.net_salary'
            )
            ->get();

        return view('salaries.report', compact(
            'totalPayroll',
            'startMonth',
            'endMonth',
            'startYear',
            'endYear',
            'totalEmployeesPaid',
            'roleSalaries',
            'salaryDetails'
        ));
    }
}
