<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 
        'name', 
        'file_path', 
        'type'
    ];

    protected $casts = [
        'type' => 'string'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Method to get attachment types
    public static function getTypes()
    {
        return [
            'machine_manual' => 'Machine Manual',
            'safety_document' => 'Safety Document',
            'site_plan' => 'Site Plan',
            'equipment_checklist' => 'Equipment Checklist',
            'insurance' => 'Insurance',
            'other' => 'Other'
        ];
    }
}
