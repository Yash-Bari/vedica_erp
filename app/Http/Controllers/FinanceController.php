<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['Admin', 'Finance'])) {
                abort(403, 'This action is unauthorized.');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Monthly Revenue
        $monthlyRevenue = Project::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('total_revenue');

        // Monthly Expenses
        $monthlyExpenses = Expense::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('amount');

        // Profit/Loss
        $profitLoss = $monthlyRevenue - $monthlyExpenses;

        // Unpaid Invoices
        $unpaidInvoices = Project::where('status', 'completed')
            ->where('total_revenue', '>', 0)
            ->count();

        return view('finance.dashboard', [
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyExpenses' => $monthlyExpenses,
            'profitLoss' => $profitLoss,
            'unpaidInvoices' => $unpaidInvoices
        ]);
    }
}
