<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    // Status Constants
    const STATUS_PENDING = 'Pending';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_ON_HOLD = 'On Hold';
    const STATUS_CANCELLED = 'Cancelled';

    // Priority Constants
    const PRIORITY_LOW = 'Low';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_HIGH = 'High';
    const PRIORITY_CRITICAL = 'Critical';

    // Invoice statuses
    public const INVOICE_STATUS_PENDING = 'Pending';
    public const INVOICE_STATUS_INVOICED = 'Invoiced';
    public const INVOICE_STATUS_PAID = 'Paid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'client_id',
        'operator_id',
        'machine_id',
        'status',
        'revenue',
        'invoice_status',
        'hourly_rate',
        'total_hours',
        'total_revenue',
        'meter_image',
        'location'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'hourly_rate' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'revenue' => 'decimal:2'
    ];

    /**
     * Relationship with Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relationship with Operator (Employee)
     */
    public function operator()
    {
        return $this->belongsTo(Employee::class, 'operator_id');
    }

    /**
     * Relationship with Machine
     */
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Relationship with Invoice
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Relationship with Project Time Logs
     */
    public function time_logs()
    {
        return $this->hasMany(ProjectTimeLog::class);
    }

    /**
     * Relationship with Latest Project Time Log
     */
    public function latestTimeLog()
    {
        return $this->hasOne(ProjectTimeLog::class)->latest();
    }

    /**
     * Relationship with Project Attachments
     */
    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class);
    }

    /**
     * Get current time log attribute
     */
    public function getCurrentTimeLogAttribute()
    {
        return $this->time_logs()
            ->whereIn('status', ['in_progress', 'on_hold'])
            ->latest()
            ->first();
    }

    /**
     * Update project status.
     *
     * @param string $newStatus
     * @return $this
     */
    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
        $this->save();
        return $this;
    }

    /**
     * Update total hours worked on the project.
     *
     * @param float $hours
     * @return $this
     */
    public function updateTotalHours($hours)
    {
        $this->total_hours += $hours;
        $this->save();
        return $this;
    }

    /**
     * Update meter image path.
     *
     * @param string $imagePath
     * @return $this
     */
    public function updateMeterImage($imagePath)
    {
        $this->meter_image = $imagePath;
        $this->save();
        return $this;
    }

    /**
     * Start the project.
     *
     * @param int $operatorId
     * @param int $machineId
     * @return void
     */
    public function startProject($operatorId, $machineId)
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'operator_id' => $operatorId,
            'machine_id' => $machineId
        ]);

        // Update machine status
        if ($this->machine) {
            $this->machine->update(['status' => 'In Use']);
        }
    }

    /**
     * Pause the project.
     *
     * @return void
     */
    public function pauseProject()
    {
        $this->update(['status' => self::STATUS_ON_HOLD]);

        // Update machine status
        if ($this->machine) {
            $this->machine->update(['status' => 'Maintenance']);
        }
    }

    /**
     * Complete the project.
     *
     * @param float $totalHours
     * @return float
     */
    public function completeProject($totalHours)
    {
        $totalRevenue = $this->hourly_rate * $totalHours;

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'total_hours' => $totalHours,
            'total_revenue' => $totalRevenue
        ]);

        // Update machine status
        if ($this->machine) {
            $this->machine->update(['status' => 'Available']);
        }

        return $totalRevenue;
    }

    /**
     * Add attachment to project.
     *
     * @param string $name
     * @param string $filePath
     * @param string $type
     * @return \App\Models\ProjectAttachment
     */
    public function addAttachment($name, $filePath, $type = 'other')
    {
        return $this->attachments()->create([
            'name' => $name,
            'file_path' => $filePath,
            'type' => $type
        ]);
    }

    /**
     * Update project totals from time logs
     */
    public function updateTotalsFromLogs()
    {
        $logs = DB::table('project_time_logs')
            ->where('project_id', $this->id)
            ->where('status', 'completed')
            ->get();

        $totalHours = 0;
        $totalRevenue = 0;

        foreach ($logs as $log) {
            $totalHours += $log->total_hours;
            // Calculate revenue for each log based on its hours
            $logRevenue = ($log->total_hours * $this->hourly_rate);
            $totalRevenue += $logRevenue;
            
            Log::info('Log revenue calculation', [
                'log_id' => $log->id,
                'total_hours' => $log->total_hours,
                'hourly_rate' => $this->hourly_rate,
                'calculated_revenue' => $logRevenue
            ]);
        }

        $oldTotalHours = $this->total_hours;
        $oldTotalRevenue = $this->total_revenue;

        $this->total_hours = round($totalHours, 2);
        $this->total_revenue = round($totalRevenue, 2);
        $this->save();

        Log::info('Project totals updated', [
            'project_id' => $this->id,
            'project_name' => $this->name,
            'old_total_hours' => $oldTotalHours,
            'new_total_hours' => $this->total_hours,
            'old_total_revenue' => $oldTotalRevenue,
            'new_total_revenue' => $this->total_revenue,
            'number_of_logs' => count($logs),
            'hourly_rate' => $this->hourly_rate
        ]);
    }

    /**
     * Calculate revenue for given minutes
     */
    public function calculateRevenueForMinutes($minutes)
    {
        // Convert minutes to decimal hours
        $hours = $minutes / 60;
        
        // Calculate revenue based on hours worked
        $revenue = $hours * $this->hourly_rate;
        
        Log::info('Revenue calculation details', [
            'project_id' => $this->id,
            'project_name' => $this->name,
            'minutes_worked' => $minutes,
            'hours_worked' => $hours,
            'hourly_rate' => $this->hourly_rate,
            'calculated_revenue' => $revenue,
            'final_revenue' => round($revenue, 2)
        ]);
        
        return round($revenue, 2);
    }

    /**
     * Get the per-minute rate for the project
     */
    public function getMinuteRateAttribute()
    {
        return round($this->hourly_rate / 60, 2);
    }

    /**
     * Get the total minutes worked on the project
     */
    public function getTotalMinutesAttribute()
    {
        return round($this->total_hours * 60);
    }
}
