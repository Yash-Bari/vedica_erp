<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Machine;
use App\Models\Employee;
use App\Models\Client;
use App\Models\MachineHealthCheck;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        try {
            // Summary Statistics
            $projectCount = Project::count();
            $employeeCount = Employee::where('role', '!=', 'Admin')->count();
            $clientCount = Client::count();
            $machineCount = Machine::count();
            
            // Active Projects
            $activeProjects = Project::where('status', 'In Progress')->count();
            
            // Machine Status Distribution
            $machineStatus = Machine::groupBy('status')
                ->select('status', DB::raw('count(*) as count'))
                ->get();
            
            // Employee Role Distribution
            $employeeRoleCounts = Employee::groupBy('role')
                ->select('role', DB::raw('count(*) as count'))
                ->get();

            // Recent Projects
            $recentProjects = Project::with('client')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            // Active Machines
            $activeMachines = Machine::where('status', 'Available')
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            // Recent Health Checks
            $recentHealthChecks = MachineHealthCheck::with(['machine', 'employee'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            return view('admin.dashboard', compact(
                'projectCount',
                'employeeCount',
                'clientCount',
                'machineCount',
                'activeProjects',
                'machineStatus',
                'employeeRoleCounts',
                'recentProjects',
                'activeMachines',
                'recentHealthChecks'
            ));
        } catch (\Exception $e) {
            \Log::error('Admin Dashboard Error: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return back()->withErrors([
                'error' => 'An error occurred while loading the dashboard. Please try again.'
            ]);
        }
    }
}
