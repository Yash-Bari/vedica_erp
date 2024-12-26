<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\SalaryPayment;
use App\Models\SalaryStructure;
use App\Models\SalaryReceipt;
use App\Policies\SalaryPaymentPolicy;
use App\Policies\SalaryStructurePolicy;
use App\Policies\SalaryReceiptPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        SalaryPayment::class => SalaryPaymentPolicy::class,
        SalaryStructure::class => SalaryStructurePolicy::class,
        SalaryReceipt::class => SalaryReceiptPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Define gates for salary-related actions
        Gate::define('view-salaries', function ($user) {
            return $user->hasRole(['admin', 'finance']);
        });

        Gate::define('process-salaries', function ($user) {
            return $user->hasRole(['admin', 'finance']);
        });

        Gate::define('manage-salary-structures', function ($user) {
            return $user->hasRole(['admin', 'finance']);
        });
    }
}
