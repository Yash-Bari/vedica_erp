<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any projects.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'manager', 'supervisor']);
    }

    /**
     * Determine whether the user can view the project.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function view(User $user, Project $project)
    {
        // Admins can view all projects
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers and supervisors can view their own projects
        if ($user->hasRole(['manager', 'supervisor'])) {
            return $project->manager_id === $user->id || 
                   $project->supervisor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create projects.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the project.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function update(User $user, Project $project)
    {
        // Admins can update all projects
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers and supervisors can update their own projects
        if ($user->hasRole(['manager', 'supervisor'])) {
            return $project->manager_id === $user->id || 
                   $project->supervisor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the project.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function delete(User $user, Project $project)
    {
        // Only admins can delete projects
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the project.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function restore(User $user, Project $project)
    {
        // Only admins can restore projects
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the project.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function forceDelete(User $user, Project $project)
    {
        // Only admins can permanently delete projects
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update project status.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function updateStatus(User $user, Project $project)
    {
        // Admins and managers can update project status
        return $user->hasRole(['admin', 'manager']) || 
               $project->manager_id === $user->id;
    }
}
