<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Machine;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    /**
     * Check machine availability for project
     *
     * @param int $machineId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return bool
     */
    public function checkMachineAvailability(int $machineId, Carbon $startDate, Carbon $endDate): bool
    {
        // Check if machine is in maintenance or repair
        $machine = Machine::findOrFail($machineId);
        if (in_array($machine->status, ['Maintenance', 'Repair'])) {
            return false;
        }

        // Check for overlapping projects
        $overlappingProjects = Project::where('machine_id', $machineId)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->whereIn('status', ['Assigned', 'In Progress'])
            ->exists();

        return !$overlappingProjects;
    }

    /**
     * Find available operators for a project
     *
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAvailableOperators(string $role = 'Operator')
    {
        return Employee::where('role', $role)
            ->where('status', 'active')
            ->whereDoesntHave('projects', function($query) {
                $query->whereIn('status', ['Assigned', 'In Progress']);
            })
            ->get();
    }

    /**
     * Assign an operator to a project
     *
     * @param Project $project
     * @param Employee|null $operator
     * @return Project
     */
    public function assignOperator(Project $project, ?Employee $operator = null)
    {
        if (!$operator) {
            // Automatically find an available operator
            $availableOperators = $this->findAvailableOperators();
            
            if ($availableOperators->isEmpty()) {
                throw ValidationException::withMessages([
                    'operator' => 'No available operators found.'
                ]);
            }

            $operator = $availableOperators->first();
        }

        $project->operator_id = $operator->id;
        $project->status = 'Assigned';
        $project->save();

        return $project;
    }

    /**
     * Create a new project with comprehensive validation
     *
     * @param array $data
     * @return Project
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createProject(array $data)
    {
        // Validate machine availability
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date'] ?? now()->addDays(7));

        if (!$this->checkMachineAvailability($data['machine_id'], $startDate, $endDate)) {
            throw ValidationException::withMessages([
                'machine_id' => 'Selected machine is not available for the specified dates.'
            ]);
        }

        // Begin transaction for atomic operation
        return DB::transaction(function () use ($data, $startDate, $endDate) {
            // Create project
            $project = Project::create([
                'project_id' => $this->generateProjectId(),
                'name' => $data['name'],
                'location' => $data['location'],
                'scope' => $data['scope'],
                'client_id' => $data['client_id'],
                'machine_id' => $data['machine_id'],
                'machine_attachment' => $data['machine_attachment'] ?? null,
                'hourly_rate' => $data['hourly_rate'],
                'platform' => $data['platform'] ?? 'Other',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'Created'
            ]);

            // Optionally assign operator
            if (isset($data['operator_id'])) {
                $operator = Employee::findOrFail($data['operator_id']);
                $this->assignOperator($project, $operator);
            }

            // Update machine status
            $machine = Machine::findOrFail($data['machine_id']);
            $machine->status = 'In Use';
            $machine->save();

            return $project;
        });
    }

    /**
     * Generate unique project ID
     *
     * @return string
     */
    private function generateProjectId(): string
    {
        $yearCode = now()->format('y');
        
        $lastProject = Project::where('project_id', 'like', $yearCode . '%')
            ->orderBy('project_id', 'desc')
            ->first();
        
        if (!$lastProject) {
            return $yearCode . '001';
        }
        
        $lastSequence = intval(substr($lastProject->project_id, 2));
        $newSequence = $lastSequence + 1;
        
        return $yearCode . str_pad($newSequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Update project status
     *
     * @param Project $project
     * @param string $status
     * @param array $additionalData
     * @return Project
     */
    public function updateProjectStatus(Project $project, string $status, array $additionalData = [])
    {
        DB::transaction(function () use ($project, $status, $additionalData) {
            switch ($status) {
                case 'In Progress':
                    $project->status = 'In Progress';
                    $project->start_date = now();
                    break;
                case 'Completed':
                    $project->status = 'Completed';
                    $project->end_date = now();
                    $project->total_hours = $additionalData['total_hours'] ?? 0;
                    $project->total_revenue = $project->hourly_rate * $project->total_hours;
                    break;
                case 'Canceled':
                    $project->status = 'Canceled';
                    $project->cancellation_reason = $additionalData['cancellation_reason'] ?? null;
                    break;
                case 'Paused':
                    $project->status = 'Paused';
                    break;
            }

            // Reset machine status if project is completed or canceled
            if (in_array($status, ['Completed', 'Canceled'])) {
                $machine = $project->machine;
                $machine->status = 'Available';
                $machine->save();
            }

            $project->save();
        });

        return $project;
    }
}
