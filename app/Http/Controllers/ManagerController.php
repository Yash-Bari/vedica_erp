<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\Machine;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function checkManagerAccess()
    {
        if (!Auth::check() || Auth::user()->role !== 'Manager') {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Manager privileges required.');
        }
        return null;
    }

    public function dashboard()
    {
        // Check manager access
        if ($redirect = $this->checkManagerAccess()) {
            return $redirect;
        }

        // Get counts for dashboard statistics
        $projectCount = Project::count();
        $employeeCount = Employee::where('role', '!=', 'Admin')->count();
        $clientCount = Client::count();
        $machineCount = Machine::count();
        
        // Get recent data
        $recentProjects = Project::with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        $activeMachines = Machine::where('status', 'Active')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $recentClients = Client::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('manager.dashboard', compact(
            'projectCount',
            'employeeCount',
            'clientCount',
            'machineCount',
            'recentProjects',
            'activeMachines',
            'recentClients'
        ));
    }

    public function employeeManagement()
    {
        if ($redirect = $this->checkManagerAccess()) {
            return $redirect;
        }

        $employees = Employee::where('role', '!=', 'Admin')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('employees.index', compact('employees'));
    }

    public function projectManagement()
    {
        if ($redirect = $this->checkManagerAccess()) {
            return $redirect;
        }

        $projects = Project::with(['client', 'machines'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function clientManagement()
    {
        if ($redirect = $this->checkManagerAccess()) {
            return $redirect;
        }

        $clients = Client::orderBy('created_at', 'desc')
            ->paginate(10);

        return view('clients.index', compact('clients'));
    }

    public function machineManagement()
    {
        if ($redirect = $this->checkManagerAccess()) {
            return $redirect;
        }

        $machines = Machine::with(['currentProject', 'healthChecks'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('machines.index', compact('machines'));
    }

    public function generateQuotation($projectId)
    {
        if ($redirect = $this->checkManagerAccess()) {
            return $redirect;
        }

        $project = Project::with(['client', 'machines'])
            ->findOrFail($projectId);

        return view('quotations.create', compact('project'));
    }
}
