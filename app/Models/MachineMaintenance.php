<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MachineMaintenance extends Model
{
    protected $fillable = [
        'machine_id', 
        'employee_id', 
        'maintenance_type',
        'priority',
        'scheduled_date',
        'actual_start_date',
        'actual_end_date',
        'status',
        'estimated_cost',
        'actual_cost',
        'description',
        'parts_replaced',
        'technician_notes',
        'before_maintenance_image',
        'after_maintenance_image',
        'warranty_claim',
        'warranty_details'
    ];

    protected $dates = [
        'scheduled_date',
        'actual_start_date',
        'actual_end_date'
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Automatically update machine status when maintenance status changes
    protected static function booted()
    {
        static::updated(function ($maintenance) {
            $maintenance->machine->updateStatus();
        });
    }

    // Calculate maintenance duration
    public function getMaintenanceDurationAttribute()
    {
        if ($this->actual_start_date && $this->actual_end_date) {
            return Carbon::parse($this->actual_start_date)
                ->diffInDays(Carbon::parse($this->actual_end_date));
        }
        return null;
    }

    // Check if maintenance is overdue
    public function isOverdue(): bool
    {
        return $this->status === 'Scheduled' && 
               Carbon::parse($this->scheduled_date)->isPast();
    }

    // Scope for active maintenance
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['Scheduled', 'In Progress']);
    }

    // Determine maintenance cost efficiency
    public function getCostEfficiencyAttribute()
    {
        if ($this->estimated_cost && $this->actual_cost) {
            return round(
                (1 - abs($this->actual_cost - $this->estimated_cost) / $this->estimated_cost) * 100, 
                2
            );
        }
        return null;
    }
}
