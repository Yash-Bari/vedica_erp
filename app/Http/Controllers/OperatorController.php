<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class OperatorController extends Controller
{
    public function dashboard()
    {
        $operator = Auth::user();

        // Current Active Project
        $currentProject = Project::where('operator_id', $operator->id)
            ->where('status', 'In Progress')
            ->first();

        // Project History
        $projectHistory = Project::where('operator_id', $operator->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('operator.dashboard', [
            'currentProject' => $currentProject,
            'projectHistory' => $projectHistory
        ]);
    }

    public function startProject(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $project->status = 'In Progress';
        $project->save();

        return redirect()->route('operator.dashboard')->with('success', 'Project started successfully');
    }

    public function endProject(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $project->status = 'Completed';
        $project->total_hours = $request->input('total_hours');
        $project->total_revenue = $project->hourly_rate * $project->total_hours;
        $project->save();

        return redirect()->route('operator.dashboard')->with('success', 'Project completed successfully');
    }
}
