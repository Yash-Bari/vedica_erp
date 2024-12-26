<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Machine;

class MachinePolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['Admin', 'Manager', 'Helper']);
    }

    public function view(User $user, Machine $machine)
    {
        // Admin and managers can view all machines
        if (in_array($user->role, ['Admin', 'Manager','Helper'])) {
            return true;
        }

        // Supervisors can view machines in their projects
        if ($user->role === 'Helper' && $machine->project) {
            return $machine->project->manager_id === $user->id;
        }

        return false;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['Admin', 'Manager']);
    }

    public function update(User $user, Machine $machine)
    {
        // Admin can update all machines
        if ($user->role === 'Admin') {
            return true;
        }

        // Managers can update machines in their projects
        if ($user->role === 'Manager' && $machine->project) {
            return $machine->project->manager_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Machine $machine)
    {
        // Only admins can delete machines
        return $user->role === 'Admin';
    }

    public function generateReports(User $user)
    {
        return in_array($user->role, ['Admin', 'Manager', 'Helper']);
    }

    public function export(User $user)
    {
        return in_array($user->role, ['Admin', 'Manager', 'Helper']);
    }
}
