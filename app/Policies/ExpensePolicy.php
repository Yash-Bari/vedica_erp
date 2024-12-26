<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Expense $expense): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Expense $expense): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Expense $expense): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Expense $expense): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can generate reports.
     */
    public function generateReports(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }

    /**
     * Determine whether the user can export.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['Admin', 'Finance']);
    }
}
