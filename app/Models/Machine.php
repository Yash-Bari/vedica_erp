<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\MachineHealthCheck;
use App\Models\MachineMaintenance;
use App\Models\Expense;

class Machine extends Model
{
    use SoftDeletes;

    // Machine Types Enum
    public const TYPES = [
        'Excavator' => 'Excavator', 
        'Bulldozer' => 'Bulldozer', 
        'Crane' => 'Crane', 
        'Dump Truck' => 'Dump Truck', 
        'Loader' => 'Loader', 
        'Compactor' => 'Compactor', 
        'Grader' => 'Grader', 
        'Roller' => 'Roller', 
        'Backhoe' => 'Backhoe', 
        'Other' => 'Other'
    ];

    // Machine Status Enum
    public const STATUS = [
        'Active' => 'Active', 
        'Maintenance' => 'Maintenance', 
        'Inactive' => 'Inactive', 
        'Repair' => 'Repair', 
        'Available' => 'Available', 
        'In Use' => 'In Use'
    ];

    protected $fillable = [
        'name', 
        'model_number', 
        'serial_number',
        'type', 
        'status',
        'project_id',
        'purchase_price', 
        'purchase_date',
        'last_maintenance_date',
        'manufacturer',
        'year_of_manufacture',
        'operating_weight',
        'fuel_capacity',
        'current_location',
        'notes'
    ];

    protected $dates = [
        'purchase_date', 
        'last_maintenance_date',
        'deleted_at'
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'machine_id');
    }

    public function healthChecks(): HasMany
    {
        return $this->hasMany(MachineHealthCheck::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(MachineMaintenance::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS['Available']);
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->whereIn('status', [
            self::STATUS['Maintenance'], 
            self::STATUS['Repair']
        ]);
    }

    // Utility Methods
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS['Available'];
    }

    public function isInMaintenance(): bool
    {
        return $this->status === self::STATUS['Maintenance'];
    }

    public function calculateAge(): ?int
    {
        return $this->year_of_manufacture 
            ? now()->year - $this->year_of_manufacture 
            : null;
    }

    public function getTotalMaintenanceCost()
    {
        return $this->expenses()
            ->where('type', 'Maintenance')
            ->sum('amount');
    }

    public function isDueForService(): bool
    {
        // Service due if last maintenance was more than 6 months ago
        return !$this->last_maintenance_date 
            || $this->last_maintenance_date->diffInMonths(now()) >= 6;
    }

    public function updateStatus()
    {
        $activeMaintenance = $this->maintenances()
            ->whereIn('status', ['Scheduled', 'In Progress'])
            ->exists();

        $criticalHealthCheck = $this->healthChecks()
            ->where('overall_condition', 'Critical')
            ->exists();

        if ($activeMaintenance) {
            $this->status = self::STATUS['Maintenance'];
        } elseif ($criticalHealthCheck) {
            $this->status = self::STATUS['Repair'];
        } else {
            $this->status = self::STATUS['Available'];
        }

        $this->save();
    }
}
