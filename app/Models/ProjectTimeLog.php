<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'machine_id',
        'start_time',
        'hold_time',
        'resume_time',
        'end_time',
        'meter_reading_start_image',
        'meter_reading_hold_image',
        'meter_reading_end_image',
        'status',
        'total_hours',
        'revenue'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'hold_time' => 'datetime',
        'resume_time' => 'datetime',
        'end_time' => 'datetime',
        'total_hours' => 'decimal:2',
        'revenue' => 'decimal:2'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
