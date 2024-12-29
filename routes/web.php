<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MachineHealthCheckController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\FinancialDashboardController;
use App\Http\Controllers\FinancialAnalyticsController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\EmployeeController;

// Root route with role-based redirection
Route::get('/', function () {
    if (Auth::check()) {
        return match(Auth::user()->role) {
            'Admin' => redirect()->route('admin.dashboard'),
            'Finance' => redirect()->route('finance.dashboard'),
            'Operator' => redirect()->route('operator.dashboard'),
            'Manager' => redirect()->route('manager.dashboard'),
            'Helper' => redirect()->route('health-checks.dashboard'),
            default => redirect()->route('login')
        };
    }
    return redirect()->route('login');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Logout and Password Management
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.renew');

    // Dashboard Route
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return match($user->role) {
            'Admin' => redirect()->route('admin.dashboard'),
            'Finance' => redirect()->route('finance.dashboard'),
            'Operator' => redirect()->route('operator.dashboard'),
            'Manager' => redirect()->route('manager.dashboard'),
            'Helper' => redirect()->route('health-checks.dashboard'),
            default => redirect()->route('login')->with('error', 'Unauthorized access')
        };
    })->name('dashboard');

    // Role-based Dashboard Routes
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/finance/dashboard', [FinanceController::class, 'dashboard'])->name('finance.dashboard');
    Route::get('/operator/dashboard', [OperatorController::class, 'dashboard'])->name('operator.dashboard');
    Route::get('/manager/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');

    // Employee Management Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/employees/register', [AuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/employees/register', [AuthController::class, 'register'])->name('employees.register.submit');
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    // Project Management Routes
    Route::resource('projects', ProjectController::class);
    Route::delete('/project-attachments/{attachment}', [ProjectController::class, 'deleteAttachment'])->name('project.attachments.delete');
    Route::get('/projects/attachments/{attachment}/download', [ProjectController::class, 'downloadAttachment'])->name('projects.download-attachment');
    Route::get('/projects/{project}/worked-time', [ProjectController::class, 'getWorkedTime'])->name('projects.worked-time');

    // Client Management Routes
    Route::resource('clients', ClientController::class);

    // Machine Management Routes
    Route::resource('machines', MachineController::class);
    
    // Health Check Routes
    Route::get('/health-checks', [MachineHealthCheckController::class, 'allMachinesHealthChecks'])
        ->name('health-checks.dashboard');
        
    Route::prefix('machines')->group(function () {
        Route::get('/{machine}/health-checks/create', [MachineHealthCheckController::class, 'create'])
            ->name('machine-health-checks.create');
        Route::post('/{machine}/health-checks', [MachineHealthCheckController::class, 'store'])
            ->name('machine-health-checks.store');
        Route::get('/{machine}/health-checks', [MachineHealthCheckController::class, 'index'])
            ->name('machine-health-checks.index');
        Route::get('/health-checks/{healthCheck}', [MachineHealthCheckController::class, 'show'])->name('machine-health-checks.show');
    });

    // Operator Work Management Routes
    Route::prefix('operator')->group(function () {
        Route::post('/project/{project}/start', [ProjectController::class, 'startProjectWork'])->name('operator.start-project');
        Route::post('/project/{project}/resume', [ProjectController::class, 'resumeProjectWork'])->name('operator.resume-project');
        Route::post('/project/{project}/hold', [ProjectController::class, 'holdProjectWork'])->name('operator.hold-project');
        Route::post('/project/{project}/stop', [ProjectController::class, 'stopProjectWork'])->name('operator.stop-project');
    });

    // Quotation Routes
    Route::resource('quotations', QuotationController::class)->except(['destroy']);

    // Financial Management Routes
    Route::prefix('financial')->group(function () {
        // Expense Management
        Route::resource('expenses', ExpenseController::class);

        // Invoice Management
        Route::resource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');
        Route::get('projects/{project}/invoice-details', [InvoiceController::class, 'fetchProjectDetails'])->name('projects.invoice-details');

        // Salary Management Routes
        Route::prefix('salaries')->name('salaries.')->group(function () {
            Route::get('/', [SalaryController::class, 'index'])->name('index');
            
            // Salary structure routes
            Route::prefix('structure')->name('structure.')->group(function () {
                Route::get('/', [SalaryController::class, 'structureIndex'])->name('index');
                Route::get('/create/{employee}', [SalaryController::class, 'structureCreate'])->name('create');
                Route::post('/', [SalaryController::class, 'structureStore'])->name('store');
                Route::get('/{structure}', [SalaryController::class, 'structureShow'])->name('show');
                Route::get('/{structure}/edit', [SalaryController::class, 'structureEdit'])->name('edit');
                Route::put('/{structure}', [SalaryController::class, 'structureUpdate'])->name('update');
                Route::delete('/{structure}', [SalaryController::class, 'structureDestroy'])->name('destroy');
            });

            // Salary payment routes
            Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('/', [SalaryController::class, 'paymentsIndex'])->name('index');
                Route::get('/process/{employee}', [SalaryController::class, 'process'])->name('process');
                Route::get('/{payment}', [SalaryController::class, 'show'])->name('show');
            });

            // Salary receipt routes
            Route::prefix('receipts')->name('receipt.')->group(function () {
                Route::get('/{receipt}', [SalaryController::class, 'downloadReceipt'])->name('show');
            });
        });

        // Financial Dashboard and Analytics
        Route::get('dashboard', [FinancialDashboardController::class, 'index'])->name('financial.dashboard');
    });
});

// Fallback route
Route::fallback(function () {
    return redirect()->route('login')->with('error', 'Page not found');
});