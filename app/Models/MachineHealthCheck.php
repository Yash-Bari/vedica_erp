<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineHealthCheck extends Model
{
    protected $fillable = [
        'machine_id', 
        'employee_id', 
        'check_date', 
        'check_time',
        'overall_condition',
        'engine_temperature',
        'oil_pressure',
        'fuel_level',
        'hydraulic_system_check',
        'electrical_system_check',
        'tire_condition_check',
        'engine_remarks',
        'hydraulic_remarks',
        'electrical_remarks',
        'tire_remarks',
        'health_check_image',
        'voice_note',
        'maintenance_recommendation'
    ];

    protected $dates = [
        'check_date'
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Determine maintenance recommendation based on check results
    public function determineMaintenanceRecommendation(): string
    {
        $criticalIssues = 0;

        if (!$this->hydraulic_system_check) $criticalIssues++;
        if (!$this->electrical_system_check) $criticalIssues++;
        if (!$this->tire_condition_check) $criticalIssues++;

        if ($this->engine_temperature > 100) $criticalIssues++;
        if ($this->oil_pressure < 20) $criticalIssues++;
        if ($this->fuel_level < 10) $criticalIssues++;

        if ($criticalIssues >= 3) {
            return 'Immediate Service';
        } elseif ($criticalIssues > 0) {
            return 'Major Repair';
        } elseif ($this->overall_condition === 'Poor') {
            return 'Minor Repair';
        }

        return 'None';
    }

    // Automatically update machine status after health check
    protected static function booted()
    {
        static::created(function ($healthCheck) {
            $healthCheck->machine->updateStatus();
        });
    }
}
