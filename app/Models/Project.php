<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    public function timeLogs()
    {
        return $this->hasMany(ProjectTimeLog::class);
    }

    /**
     * Alias for compatibility
     */
    public function timeLog()
    {
        return $this->timeLogs();
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
        $totals = $this->timeLog()
            ->selectRaw('SUM(total_hours) as total_hours, SUM(revenue) as total_revenue')
            ->first();

        $this->update([
            'total_hours' => $totals->total_hours ?? 0,
            'total_revenue' => $totals->total_revenue ?? 0
        ]);

        return $this;
    }
}
