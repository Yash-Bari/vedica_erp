<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SalaryStructure;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalaryStructurePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any salary structures.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can view the salary structure.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SalaryStructure  $salaryStructure
     * @return mixed
     */
    public function view(User $user, SalaryStructure $salaryStructure)
    {
        return $user->hasRole(['admin', 'finance']) || 
               $user->employee_id === $salaryStructure->employee_id;
    }

    /**
     * Determine whether the user can create salary structures.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can update the salary structure.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SalaryStructure  $salaryStructure
     * @return mixed
     */
    public function update(User $user, SalaryStructure $salaryStructure)
    {
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine whether the user can delete the salary structure.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SalaryStructure  $salaryStructure
     * @return mixed
     */
    public function delete(User $user, SalaryStructure $salaryStructure)
    {
        return $user->hasRole(['admin', 'finance']);
    }
}
