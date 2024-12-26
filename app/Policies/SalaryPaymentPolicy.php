<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SalaryPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalaryPaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any salary payments.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can view the salary payment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SalaryPayment  $salaryPayment
     * @return mixed
     */
    public function view(User $user, SalaryPayment $salaryPayment)
    {
        return $user->hasRole(['admin', 'finance']) || 
               $user->employee_id === $salaryPayment->employee_id;
    }

    /**
     * Determine whether the user can process salaries.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function processSalaries(User $user)
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can mark salaries as paid.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function markAsPaid(User $user)
    {
        return $user->hasRole(['admin', 'finance']);
    }
}
