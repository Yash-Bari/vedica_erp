<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjectTimeLogController extends Controller
{
    public function startProject(Request $request, Project $project)
    {
        $request->validate([
            'meter_reading_start_image' => 'required|image|max:2048',
            'meter_reading_start' => 'required|numeric|min:0'
        ]);

        // Store meter reading start image
        $meterStartImagePath = $request->file('meter_reading_start_image')
            ->store('meter-readings', 'public');

        // Create time log entry
        $timeLog = new ProjectTimeLog([
            'project_id' => $project->id,
            'machine_id' => $project->machine_id,
            'start_time' => now(),
            'meter_reading_start' => $request->meter_reading_start,
            'meter_reading_start_image' => $meterStartImagePath,
            'status' => 'in_progress'
        ]);
        $timeLog->save();

        // Update project status
        $project->status = 'in_progress';
        $project->save();

        return redirect()->back()->with('success', 'Project started successfully');
    }

    public function holdProject(Request $request, Project $project)
    {
        $request->validate([
            'meter_reading_hold_image' => 'required|image|max:2048'
        ]);

        // Get current time log
        $timeLog = $project->currentTimeLog;
        if (!$timeLog) {
            return redirect()->back()->with('error', 'No active time log found');
        }

        // Store meter reading hold image
        $meterHoldImagePath = $request->file('meter_reading_hold_image')
            ->store('meter-readings', 'public');

        // Update time log
        $timeLog->hold_time = now();
        $timeLog->meter_reading_hold_image = $meterHoldImagePath;
        $timeLog->status = 'on_hold';
        $timeLog->save();

        // Update project status
        $project->status = 'on_hold';
        $project->save();

        return redirect()->back()->with('success', 'Project put on hold');
    }

    public function resumeProject(Request $request, Project $project)
    {
        // Get current time log
        $timeLog = $project->currentTimeLog;
        if (!$timeLog) {
            return redirect()->back()->with('error', 'No active time log found');
        }

        // Update time log
        $timeLog->resume_time = now();
        $timeLog->status = 'in_progress';
        $timeLog->save();

        // Update project status
        $project->status = 'in_progress';
        $project->save();

        return redirect()->back()->with('success', 'Project resumed');
    }

    public function endProject(Request $request, Project $project)
    {
        $request->validate([
            'meter_reading_end_image' => 'required|image|max:2048',
            'meter_reading_end' => 'required|numeric|min:0'
        ]);

        // Get current time log
        $timeLog = $project->currentTimeLog;
        if (!$timeLog) {
            Log::error('No active time log found', ['project_id' => $project->id]);
            return redirect()->back()->with('error', 'No active time log found');
        }

        try {
            DB::beginTransaction();

            // Store meter reading end image
            $meterEndImagePath = $request->file('meter_reading_end_image')
                ->store('meter-readings', 'public');

            // Set end time
            $timeLog->end_time = now();
            
            // Calculate total minutes worked
            $totalMinutes = $this->calculateTotalMinutes($timeLog);
            $totalHours = round($totalMinutes / 60, 2);
            
            // Calculate revenue based on hours worked
            $revenue = $project->calculateRevenueForMinutes($totalMinutes);
            
            Log::info('Time log completion details', [
                'time_log_id' => $timeLog->id,
                'project_id' => $project->id,
                'total_minutes' => $totalMinutes,
                'total_hours' => $totalHours,
                'hourly_rate' => $project->hourly_rate,
                'calculated_revenue' => $revenue
            ]);
            
            // Update time log
            $timeLog->meter_reading_end = $request->meter_reading_end;
            $timeLog->meter_reading_end_image = $meterEndImagePath;
            $timeLog->total_hours = $totalHours;
            $timeLog->revenue = $revenue;
            $timeLog->status = 'completed';
            $timeLog->save();

            // Update project status and totals
            $project->status = 'completed';
            $project->updateTotalsFromLogs();

            DB::commit();
            return redirect()->back()->with('success', 'Project completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing project', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error completing project');
        }
    }

    private function calculateTotalMinutes($timeLog)
    {
        $totalMinutes = 0;
        
        if ($timeLog->hold_time) {
            // First period: start to hold
            $firstPeriod = $timeLog->start_time->diffInSeconds($timeLog->hold_time) / 60;
            
            // Second period: resume to end (if applicable)
            $secondPeriod = 0;
            if ($timeLog->resume_time) {
                if ($timeLog->end_time) {
                    $secondPeriod = $timeLog->resume_time->diffInSeconds($timeLog->end_time) / 60;
                } else {
                    $secondPeriod = $timeLog->resume_time->diffInSeconds(now()) / 60;
                }
            }
            
            $totalMinutes = $firstPeriod + $secondPeriod;
            
            Log::info('Time periods', [
                'first_period' => $firstPeriod,
                'second_period' => $secondPeriod
            ]);
        } else {
            // No hold time, calculate from start to end/now
            if ($timeLog->end_time) {
                $totalMinutes = $timeLog->start_time->diffInSeconds($timeLog->end_time) / 60;
            } else {
                $totalMinutes = $timeLog->start_time->diffInSeconds(now()) / 60;
            }
        }
        
        Log::info('Time calculations', [
            'start_time' => $timeLog->start_time,
            'hold_time' => $timeLog->hold_time,
            'resume_time' => $timeLog->resume_time,
            'end_time' => $timeLog->end_time,
            'total_minutes' => $totalMinutes
        ]);
        
        return $totalMinutes;
    }
}
