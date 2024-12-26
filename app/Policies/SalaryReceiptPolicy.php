<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SalaryReceipt;

class SalaryReceiptPolicy
{
    /**
     * Determine if the user can view the salary receipt.
     */
    public function view(User $user, SalaryReceipt $salaryReceipt)
    {
        // Only admin and finance roles can view salary receipts
        return $user->hasRole(['admin', 'finance']);
    }

    /**
     * Determine if the user can access salary receipts in general.
     */
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'finance']);
    }
}
