<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\MachineHealthCheck;
use App\Models\MachineMaintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MachineHealthCheckService
{
    /**
     * Perform a machine health check
     *
     * @param array $data
     * @return MachineHealthCheck
     */
    public function performHealthCheck(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validate and process image upload
            if (isset($data['health_check_image'])) {
                $imagePath = $this->uploadHealthCheckImage($data['health_check_image'], $data['machine_id']);
                $data['health_check_image'] = $imagePath;
            }

            // Validate and process voice note upload
            if (isset($data['voice_note'])) {
                $voicePath = $this->uploadVoiceNote($data['voice_note'], $data['machine_id']);
                $data['voice_note'] = $voicePath;
            }

            // Create health check
            $healthCheck = MachineHealthCheck::create($data);

            // Determine maintenance recommendation based on overall condition
            $maintenanceRecommendation = match($data['overall_condition']) {
                'Critical', 'Poor' => 'Immediate',
                'Fair' => 'Scheduled',
                'Good', 'Excellent' => 'None',
                default => 'None',
            };

            $healthCheck->maintenance_recommendation = $maintenanceRecommendation;
            $healthCheck->save();

            // Update machine maintenance need
            $machine = Machine::findOrFail($data['machine_id']);
            $machine->maintenance_need = $this->mapMaintenanceRecommendation($maintenanceRecommendation);
            $machine->save();

            // Create maintenance record if needed
            if ($maintenanceRecommendation !== 'None') {
                $this->createMaintenanceRecord($machine, $maintenanceRecommendation, $healthCheck);
            }

            return $healthCheck;
        });
    }

    /**
     * Upload health check image
     *
     * @param mixed $image
     * @param int $machineId
     * @return string
     */
    private function uploadHealthCheckImage($image, int $machineId): string
    {
        $filename = 'machine_' . $machineId . '_health_check_' . now()->timestamp . '.' . $image->getClientOriginalExtension();
        return $image->storeAs('machine_health_checks', $filename, 'public');
    }

    /**
     * Upload voice note
     *
     * @param mixed $voiceNote
     * @param int $machineId
     * @return string
     */
    private function uploadVoiceNote($voiceNote, int $machineId): string
    {
        $filename = 'machine_' . $machineId . '_voice_note_' . now()->timestamp . '.' . $voiceNote->getClientOriginalExtension();
        return $voiceNote->storeAs('machine_voice_notes', $filename, 'public');
    }

    /**
     * Map maintenance recommendation to machine maintenance need
     */
    private function mapMaintenanceRecommendation($recommendation)
    {
        return match($recommendation) {
            'Immediate' => 'Urgent',
            'Scheduled' => 'Scheduled',
            'None' => null,
            default => null,
        };
    }

    /**
     * Create maintenance record based on health check
     *
     * @param Machine $machine
     * @param string $maintenanceRecommendation
     * @param MachineHealthCheck $healthCheck
     * @return MachineMaintenance
     */
    private function createMaintenanceRecord(
        Machine $machine, 
        string $maintenanceRecommendation, 
        MachineHealthCheck $healthCheck
    ): MachineMaintenance {
        $priority = match($maintenanceRecommendation) {
            'Immediate' => 'Urgent',
            'Scheduled' => 'Medium',
            default => 'Low'
        };

        return MachineMaintenance::create([
            'machine_id' => $machine->id,
            'employee_id' => $healthCheck->employee_id,
            'maintenance_type' => 'Preventive',
            'priority' => $priority,
            'scheduled_date' => now()->addDays($this->calculateScheduleDays($priority)),
            'status' => 'Scheduled',
            'description' => 'Maintenance based on health check: ' . $healthCheck->engine_remarks,
            'estimated_cost' => $this->estimateMaintenanceCost($priority)
        ]);
    }

    /**
     * Calculate days to schedule maintenance
     *
     * @param string $priority
     * @return int
     */
    private function calculateScheduleDays(string $priority): int
    {
        return match($priority) {
            'Urgent' => 1,
            'High' => 3,
            'Medium' => 7,
            default => 14
        };
    }

    /**
     * Estimate maintenance cost based on priority
     *
     * @param string $priority
     * @return float
     */
    private function estimateMaintenanceCost(string $priority): float
    {
        return match($priority) {
            'Urgent' => 5000.00,
            'High' => 3000.00,
            'Medium' => 1500.00,
            default => 500.00
        };
    }
}
