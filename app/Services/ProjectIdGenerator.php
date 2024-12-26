<?php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;

class ProjectIdGenerator
{
    public static function generateProjectId(): string
    {
        // Get last two digits of current year
        $yearCode = Carbon::now()->format('y');
        
        // Find the last project ID for this year
        $lastProject = Project::where('project_id', 'like', $yearCode . '%')
            ->orderBy('project_id', 'desc')
            ->first();
        
        if (!$lastProject) {
            // If no projects exist for this year, start with 001
            return $yearCode . '001';
        }
        
        // Extract the sequence number and increment
        $lastSequence = intval(substr($lastProject->project_id, 2));
        $newSequence = $lastSequence + 1;
        
        // Pad with zeros to maintain 3-digit format
        return $yearCode . str_pad($newSequence, 3, '0', STR_PAD_LEFT);
    }
}
