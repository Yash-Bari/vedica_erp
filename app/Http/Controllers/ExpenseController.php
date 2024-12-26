<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::query();

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->ofCategory($request->category);
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->betweenDates($request->start_date, $request->end_date);
        }

        // Sorting
        $query->orderBy($request->get('sort_by', 'date'), 
                        $request->get('sort_direction', 'desc'));

        $expenses = $query->paginate(15);
        $projects = Project::all();

        return view('expenses.index', compact('expenses', 'projects'));
    }

    /**
     * Show the form for creating a new expense
     */
    public function create()
    {
        $this->authorize('create', Expense::class);

        $projects = Project::all();
        return view('expenses.create', compact('projects'));
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        $this->authorize('create', Expense::class);

        $validatedData = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date|before_or_equal:today',
            'type' => ['required', Rule::in([
                Expense::TYPE_MATERIAL,
                Expense::TYPE_LABOR,
                Expense::TYPE_EQUIPMENT,
                Expense::TYPE_TRANSPORTATION,
                Expense::TYPE_MISCELLANEOUS
            ])],
            'category' => ['required', Rule::in([Expense::CATEGORY_DIRECT, Expense::CATEGORY_INDIRECT])],
            'description' => 'nullable|string|max:500',
            'vendor_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|unique:expenses',
            'payment_method' => ['nullable', Rule::in(['Cash', 'Bank Transfer', 'Credit Card', 'Debit Card', 'Cheque'])],
            'employee_id' => 'nullable|exists:employees,id',
            'machine_id' => 'nullable|exists:machines,id'
        ]);

        $expense = Expense::create($validatedData);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense recorded successfully');
    }

    /**
     * Display specific expense details
     */
    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Edit an existing expense
     */
    public function edit(Expense $expense)
    {
        $this->authorize('update', $expense);

        $projects = Project::all();
        return view('expenses.edit', compact('expense', 'projects'));
    }

    /**
     * Update an existing expense
     */
    public function update(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        $validatedData = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date|before_or_equal:today',
            'type' => ['required', Rule::in([
                Expense::TYPE_MATERIAL,
                Expense::TYPE_LABOR,
                Expense::TYPE_EQUIPMENT,
                Expense::TYPE_TRANSPORTATION,
                'Miscellaneous'
            ])],
            'category' => ['required', Rule::in(['Direct', 'Indirect'])],
            'description' => 'nullable|string|max:500',
            'vendor_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|unique:expenses,invoice_number,' . $expense->id,
            'payment_method' => ['nullable', Rule::in(['Cash', 'Bank Transfer', 'Credit Card', 'Debit Card', 'Cheque'])],
            'employee_id' => 'nullable|exists:employees,id',
            'machine_id' => 'nullable|exists:machines,id'
        ]);

        $expense->update($validatedData);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense updated successfully');
    }

    /**
     * Delete an expense
     */
    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully');
    }

    /**
     * Generate expense report
     */
    public function report(Request $request)
    {
        $this->authorize('generateReports', Expense::class);

        $startDate = $request->input('start_date', now()->startOfYear());
        $endDate = $request->input('end_date', now());

        $totalExpenses = Expense::calculateTotalExpenses($startDate, $endDate);
        $expensesByType = Expense::select('type')
            ->selectRaw('SUM(amount) as total_amount')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('type')
            ->get();

        return view('expenses.report', compact('totalExpenses', 'expensesByType', 'startDate', 'endDate'));
    }

    /**
     * Export expenses to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('export', Expense::class);

        $startDate = $request->input('start_date', now()->startOfYear());
        $endDate = $request->input('end_date', now());

        $expenses = Expense::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        // Generate CSV file
        $filename = 'expenses_' . now()->format('YmdHis') . '.csv';
        $handle = fopen($filename, 'w');

        // CSV headers
        fputcsv($handle, [
            'ID', 'Project', 'Amount', 'Date', 
            'Type', 'Category', 'Vendor', 
            'Invoice Number', 'Payment Method'
        ]);

        // CSV rows
        foreach ($expenses as $expense) {
            fputcsv($handle, [
                $expense->id,
                $expense->project ? $expense->project->name : 'N/A',
                $expense->amount,
                $expense->date->format('Y-m-d'),
                $expense->type,
                $expense->category,
                $expense->vendor_name,
                $expense->invoice_number,
                $expense->payment_method
            ]);
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Get the CSS class for an expense type
     */
    private function getExpenseTypeClass($type): string
    {
        return match($type) {
            Expense::TYPE_MATERIAL => 'bg-blue-100 text-blue-800',
            Expense::TYPE_LABOR => 'bg-green-100 text-green-800',
            Expense::TYPE_EQUIPMENT => 'bg-yellow-100 text-yellow-800',
            Expense::TYPE_TRANSPORTATION => 'bg-purple-100 text-purple-800',
            Expense::TYPE_MISCELLANEOUS => 'bg-pink-100 text-pink-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
