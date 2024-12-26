<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

class FinancialAnalyticsController extends Controller
{
    /**
     * Display financial analytics dashboard
     */
    public function dashboard()
    {
        // Get current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Monthly Revenue
        $monthlyRevenue = Project::whereYear('created_at', $currentYear)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_revenue) as revenue')
            )
            ->groupBy('month')
            ->get();

        // Monthly Expenses
        $monthlyExpenses = Expense::whereYear('date', $currentYear)
            ->select(
                DB::raw('MONTH(date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->get();

        // Project Profitability
        $projectProfitability = Project::where('status', 'Completed')
            ->get()
            ->map(function ($project) {
                return [
                    'name' => $project->name,
                    'revenue' => $project->total_revenue,
                    'expenses' => $project->getTotalExpenses(),
                    'profit' => $project->calculateProfit(),
                    'margin' => $project->getProfitMargin()
                ];
            });

        // Outstanding Payments
        $outstandingPayments = Invoice::where('status', 'Unpaid')
            ->orWhere('status', 'Partially Paid')
            ->sum('remaining_amount');

        // Expense Categories Distribution
        $expenseDistribution = Expense::whereYear('date', $currentYear)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        // Monthly Salary Expenses
        $salaryExpenses = Salary::whereYear('month_year', $currentYear)
            ->select(
                DB::raw('MONTH(month_year) as month'),
                DB::raw('SUM(net_amount) as total')
            )
            ->groupBy('month')
            ->get();

        // Calculate Key Performance Indicators
        $kpis = [
            'total_revenue' => Project::whereYear('created_at', $currentYear)->sum('total_revenue'),
            'total_expenses' => Expense::whereYear('date', $currentYear)->sum('amount'),
            'total_profit' => Project::whereYear('created_at', $currentYear)->get()->sum(function ($project) {
                return $project->calculateProfit();
            }),
            'average_profit_margin' => Project::where('status', 'Completed')
                ->whereYear('created_at', $currentYear)
                ->get()
                ->avg(function ($project) {
                    return $project->getProfitMargin();
                })
        ];

        return view('financial.dashboard', compact(
            'monthlyRevenue',
            'monthlyExpenses',
            'projectProfitability',
            'outstandingPayments',
            'expenseDistribution',
            'salaryExpenses',
            'kpis'
        ));
    }

    /**
     * Generate profit/loss statement
     */
    public function profitLossStatement(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month');

        $query = DB::table('projects')
            ->whereYear('created_at', $year);

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        // Revenue
        $revenue = [
            'project_revenue' => $query->sum('total_revenue'),
            'other_income' => 0 // Add other income sources if any
        ];

        // Expenses Query
        $expenseQuery = DB::table('expenses')
            ->whereYear('date', $year);
        
        if ($month) {
            $expenseQuery->whereMonth('date', $month);
        }

        // Expenses by Category
        $expenses = $expenseQuery
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        // Salary Expenses
        $salaryQuery = DB::table('salaries')
            ->whereYear('month_year', $year);
        
        if ($month) {
            $salaryQuery->whereMonth('month_year', $month);
        }

        $salaryExpenses = $salaryQuery->sum('net_amount');

        // Calculate Totals
        $totalRevenue = array_sum($revenue);
        $totalExpenses = $expenses->sum('total') + $salaryExpenses;
        $netProfit = $totalRevenue - $totalExpenses;

        return view('financial.profit-loss', compact(
            'year',
            'month',
            'revenue',
            'expenses',
            'salaryExpenses',
            'totalRevenue',
            'totalExpenses',
            'netProfit'
        ));
    }

    /**
     * Generate financial forecasts
     */
    public function forecast()
    {
        // Get historical data
        $historicalRevenue = Project::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_revenue) as revenue')
        )
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        // Calculate growth rates and trends
        $growthRates = collect();
        for ($i = 1; $i < $historicalRevenue->count(); $i++) {
            $previousRevenue = $historicalRevenue[$i - 1]->revenue;
            $currentRevenue = $historicalRevenue[$i]->revenue;
            
            if ($previousRevenue > 0) {
                $growthRate = (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
                $growthRates->push($growthRate);
            }
        }

        // Calculate average growth rate
        $averageGrowthRate = $growthRates->avg();

        // Generate 6-month forecast
        $lastMonth = $historicalRevenue->last();
        $forecast = collect();
        $projectedRevenue = $lastMonth->revenue;

        for ($i = 1; $i <= 6; $i++) {
            $projectedRevenue *= (1 + ($averageGrowthRate / 100));
            $forecast->push([
                'month' => Carbon::create($lastMonth->year, $lastMonth->month)->addMonths($i),
                'projected_revenue' => $projectedRevenue
            ]);
        }

        return view('financial.forecast', compact(
            'historicalRevenue',
            'forecast',
            'averageGrowthRate'
        ));
    }

    /**
     * Advanced financial filtering and reporting
     */
    public function advancedReport(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'platforms' => 'nullable|array',
            'min_revenue' => 'nullable|numeric|min:0',
            'max_revenue' => 'nullable|numeric',
            'expense_categories' => 'nullable|array',
            'project_status' => 'nullable|in:Completed,In Progress,Pending',
            'export_format' => 'nullable|in:pdf,csv,xlsx'
        ]);

        // Base query for projects
        $projectQuery = Project::query();

        // Apply filters
        if ($request->filled('start_date')) {
            $projectQuery->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $projectQuery->where('created_at', '<=', $request->end_date);
        }

        if ($request->filled('platforms')) {
            $projectQuery->whereIn('platform', $request->platforms);
        }

        if ($request->filled('min_revenue')) {
            $projectQuery->where('total_revenue', '>=', $request->min_revenue);
        }

        if ($request->filled('max_revenue')) {
            $projectQuery->where('total_revenue', '<=', $request->max_revenue);
        }

        if ($request->filled('project_status')) {
            $projectQuery->where('status', $request->project_status);
        }

        // Fetch filtered projects
        $projects = $projectQuery->get();

        // Expense filtering
        $expenseQuery = Expense::query();
        if ($request->filled('start_date')) {
            $expenseQuery->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $expenseQuery->where('date', '<=', $request->end_date);
        }

        if ($request->filled('expense_categories')) {
            $expenseQuery->whereIn('category', $request->expense_categories);
        }

        $expenses = $expenseQuery->get();

        // Prepare export if requested
        if ($request->filled('export_format')) {
            $exportData = [
                'projects' => $projects,
                'expenses' => $expenses,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ];

            return $this->exportReport($exportData, $request->export_format);
        }

        // Return view for web display
        return view('financial.advanced-report', [
            'projects' => $projects,
            'expenses' => $expenses,
            'filters' => $validated
        ]);
    }

    /**
     * Export financial report
     */
    private function exportReport($data, $format)
    {
        $filename = 'financial_report_' . now()->format('YmdHis');

        switch ($format) {
            case 'pdf':
                $pdf = PDF::loadView('exports.financial-report-pdf', $data);
                return $pdf->download("{$filename}.pdf");
            
            case 'csv':
                return Excel::download(
                    new FinancialReportExport($data), 
                    "{$filename}.csv"
                );
            
            case 'xlsx':
                return Excel::download(
                    new FinancialReportExport($data), 
                    "{$filename}.xlsx"
                );
            
            default:
                abort(400, 'Invalid export format');
        }
    }

    /**
     * Get unique values for filtering
     */
    public function getFilterOptions()
    {
        return response()->json([
            'platforms' => Project::distinct('platform')->pluck('platform'),
            'expense_categories' => Expense::distinct('category')->pluck('category'),
            'project_statuses' => ['Completed', 'In Progress', 'Pending']
        ]);
    }
}
