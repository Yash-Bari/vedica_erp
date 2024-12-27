<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Machine;
use App\Models\Project;
use App\Models\ProjectAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects
     */
    public function index(Request $request)
    {
        $query = Project::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Filter by client
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Search by name or client
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('client_name', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $projects = $query->paginate(10);
        
        // Get all clients for the filter dropdown
        $clients = Client::all();

        return view('projects.index', [
            'projects' => $projects,
            'clients' => $clients,
            'statuses' => [
                Project::STATUS_PENDING => 'Pending',
                Project::STATUS_IN_PROGRESS => 'In Progress',
                Project::STATUS_COMPLETED => 'Completed',
                Project::STATUS_ON_HOLD => 'On Hold',
                Project::STATUS_CANCELLED => 'Cancelled'
            ],
            'priorities' => [
                Project::PRIORITY_LOW => 'Low',
                Project::PRIORITY_MEDIUM => 'Medium',
                Project::PRIORITY_HIGH => 'High',
                Project::PRIORITY_CRITICAL => 'Critical'
            ]
        ]);
    }

    public function create()
    {
        $clients = Client::all();
        
        // Get operators who are not assigned to any active project
        $operators = Employee::where('role', 'Operator')
            ->whereDoesntHave('projects', function($query) {
                $query->whereIn('status', ['created', 'in_progress', 'on_hold']);
            })
            ->get()
            ->map(function($operator) {
                return [
                    'id' => $operator->id,
                    'name' => $operator->first_name . ' ' . $operator->last_name
                ];
            });
    
        // Get machines that are available and not assigned to any project
        $machines = Machine::where('status', 'Available')
            ->whereDoesntHave('projects', function($query) {
                $query->whereIn('status', ['created', 'in_progress', 'on_hold']);
            })
            ->get();
    
        $attachmentTypes = ProjectAttachment::getTypes();
    
        return view('projects.create', compact(
            'clients', 
            'operators', 
            'machines', 
            'attachmentTypes'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'hourly_rate' => 'required|numeric|min:0',
            'operator_id' => 'required|exists:employees,id',
            'machine_id' => 'required|exists:machines,id',
            'location' => 'required|string|max:255',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // Max 10MB per file
            'attachment_types' => 'nullable|array',
            'attachment_types.*' => 'in:' . implode(',', array_keys(ProjectAttachment::getTypes()))
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        // Create project
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'client_id' => $request->client_id,
            'hourly_rate' => $request->hourly_rate,
            'operator_id' => $request->operator_id,
            'machine_id' => $request->machine_id,
            'location' => $request->location,
            'status' => 'created'
        ]);
    
        // Update the machine's project_id when a machine is assigned
        if ($request->machine_id) {
            $machine = Machine::find($request->machine_id);
            $machine->project_id = $project->id;
            $machine->save();
        }
    
        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $index => $file) {
                // Generate unique filename
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('project_attachments', $filename, 'public');
    
                // Get corresponding attachment type
                $type = $request->attachment_types[$index] ?? 'other';
    
                // Create attachment record
                $project->addAttachment(
                    $file->getClientOriginalName(), 
                    $path, 
                    $type
                );
            }
        }
    
        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully');
    }

    /**
     * Start a project
     */
    public function startProject(Request $request, $projectId)
    {
        $validator = Validator::make($request->all(), [
            'operator_id' => 'required|exists:employees,id',
            'machine_id' => 'required|exists:machines,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::findOrFail($projectId);
        $project->startProject(
            $request->operator_id, 
            $request->machine_id
        );

        return response()->json([
            'success' => true, 
            'message' => 'Project started successfully'
        ]);
    }

    /**
     * Pause a project
     */
    public function pauseProject($projectId)
    {
        $project = Project::findOrFail($projectId);
        $project->pauseProject();

        return response()->json([
            'success' => true, 
            'message' => 'Project paused successfully'
        ]);
    }

    /**
     * Complete a project
     */
    public function completeProject(Request $request, $projectId)
    {
        $validator = Validator::make($request->all(), [
            'total_hours' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::findOrFail($projectId);
        $totalRevenue = $project->completeProject($request->total_hours);

        return response()->json([
            'success' => true, 
            'message' => 'Project completed successfully',
            'total_revenue' => $totalRevenue
        ]);
    }

    /**
     * Display the specified project
     */
    public function show(Project $project)
    {
        $project->load([
            'client',
            'operator',
            'machine',
            'time_logs',
            'attachments'
        ]);

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the project
     */
    public function edit(Project $project)
    {
        // Load the project with its attachments
        $project->load('attachments');

        // Get supervisors and managers
        $supervisors = Employee::whereIn('role', ['Supervisor', 'Admin'])->get();
        $managers = Employee::whereIn('role', ['Manager', 'Admin'])->get();
        $machines = Machine::all();

        return view('projects.edit', [
            'project' => $project,
            'supervisors' => $supervisors,
            'managers' => $managers,
            'machines' => $machines,
            'statuses' => [
                Project::STATUS_PENDING => 'Pending',
                Project::STATUS_IN_PROGRESS => 'In Progress',
                Project::STATUS_COMPLETED => 'Completed',
                Project::STATUS_ON_HOLD => 'On Hold',
                Project::STATUS_CANCELLED => 'Cancelled'
            ],
            'priorities' => [
                Project::PRIORITY_LOW => 'Low',
                Project::PRIORITY_MEDIUM => 'Medium',
                Project::PRIORITY_HIGH => 'High',
                Project::PRIORITY_CRITICAL => 'Critical'
            ]
        ]);
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'attachments.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update project
        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Generate unique filename
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('project_attachments', $filename, 'public');

                // Create attachment record
                $project->addAttachment(
                    $file->getClientOriginalName(),
                    $path
                );
            }
        }

        // Update the machine's project_id when a machine is assigned
        if ($request->machine_id) {
            $machine = Machine::find($request->machine_id);
            $machine->project_id = $project->id;
            $machine->save();
        }

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully');
    }

    /**
     * Update project status
     */
    public function updateStatus(Project $project, Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:created,in_progress,hold,completed',
            'total_hours' => 'nullable|numeric',
            'meter_image' => 'nullable|image|max:2048'
        ]);

        // Update project status
        $project->updateStatus($validatedData['status']);

        // Update total hours if provided
        if (isset($validatedData['total_hours'])) {
            $project->updateTotalHours($validatedData['total_hours']);
        }

        // Handle meter image upload
        if ($request->hasFile('meter_image')) {
            $imagePath = $request->file('meter_image')->store('meter_images', 'public');
            $project->updateMeterImage($imagePath);
        }

        // Update machine status based on project status
        if ($project->machine) {
            switch ($validatedData['status']) {
                case 'in_progress':
                    $project->machine->update(['status' => 'In Use']);
                    break;
                case 'completed':
                    $project->machine->update(['status' => 'Available']);
                    break;
                case 'hold':
                    $project->machine->update(['status' => 'Maintenance']);
                    break;
            }
        }

        return redirect()->back()->with('success', 'Project status updated successfully');
    }

    /**
     * Delete the specified project
     */
    public function destroy(Project $project)
    {
        // Soft delete
        $project->delete();

        // Log deletion
        activity()
            ->performedOn($project)
            ->causedBy(Auth::user())
            ->log('Project deleted');

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully');
    }

    /**
     * Generate project performance metrics
     */
    public function performanceMetrics(Project $project)
    {
        // Calculate project progress metrics
        $totalExpenses = $project->expenses()->sum('amount');
        $machineUtilization = $project->machines()->count();
        $completedTasks = $project->tasks()->where('status', 'Completed')->count();
        $totalTasks = $project->tasks()->count();

        // Calculate progress percentage
        $progressPercentage = $totalTasks > 0 
            ? round(($completedTasks / $totalTasks) * 100, 2) 
            : 0;

        // Budget utilization
        $budgetUtilization = $project->budget > 0 
            ? round(($totalExpenses / $project->budget) * 100, 2)
            : 0;

        return view('projects.performance', [
            'project' => $project,
            'totalExpenses' => $totalExpenses,
            'machineUtilization' => $machineUtilization,
            'progressPercentage' => $progressPercentage,
            'budgetUtilization' => $budgetUtilization,
            'completedTasks' => $completedTasks,
            'totalTasks' => $totalTasks
        ]);
    }

    /**
     * Detailed machine allocation tracking
     */
    public function machineAllocation(Project $project)
    {
        $allocatedMachines = $project->machines()->with([
            'maintenances' => function($query) {
                $query->recent()->limit(3);
            },
            'healthChecks' => function($query) {
                $query->recent()->limit(3);
            }
        ])->get();

        return view('projects.machine-allocation', [
            'project' => $project,
            'allocatedMachines' => $allocatedMachines
        ]);
    }

    /**
     * Generate comprehensive expense summary
     */
    public function expenseSummary(Project $project)
    {
        $expenseCategories = $project->expenses()
            ->select('category', \DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->get();

        $monthlyExpenses = $project->expenses()
            ->select(
                \DB::raw('MONTH(date) as month'), 
                \DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('projects.expense-summary', [
            'project' => $project,
            'expenseCategories' => $expenseCategories,
            'monthlyExpenses' => $monthlyExpenses,
            'totalExpenses' => $project->expenses()->sum('amount')
        ]);
    }

    /**
     * Start/Resume project work
     */
    public function startProjectWork(Request $request, Project $project)
    {
        try {
            $request->validate([
                'meter_reading_image' => 'required|image|max:5120', // Max 5MB
            ]);

            // Get the operator employee
            $employee = Employee::where('role', 'Operator')
                ->where('status', 'Active')
                ->first();

            if (!$employee) {
                return redirect()->route('operator.dashboard')
                    ->with('error', 'No active operator account found.');
            }

            // Verify project is assigned to this operator
            if ($project->operator_id !== $employee->id) {
                return redirect()->route('operator.dashboard')
                    ->with('error', 'You are not assigned to this project.');
            }

            // Store the meter reading image
            $imagePath = null;
            if ($request->hasFile('meter_reading_image')) {
                $file = $request->file('meter_reading_image');
                $fileName = 'start_' . time() . '_' . $project->id . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('meter-readings', $fileName, 'public');
            }

            if (!$imagePath) {
                throw new \Exception('Failed to save meter reading image.');
            }

            // Create new time log entry
            $project->time_logs()->create([
                'start_time' => now(),
                'meter_reading_start_image' => $imagePath,
                'status' => 'in_progress',
                'machine_id' => $project->machine_id,
                'project_id' => $project->id
            ]);

            // Update project status
            $project->update(['status' => 'in_progress']);

            return redirect()->route('operator.dashboard')
                ->with('success', 'Project work started successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error starting project work: ' . $e->getMessage());
            return redirect()->route('operator.dashboard')
                ->with('error', 'Failed to start project work. Please try again.');
        }
    }

    /**
     * Resume project work (no image required)
     */
    public function resumeProjectWork(Request $request, Project $project)
    {
        try {
            // Get the operator employee
            $employee = Employee::where('role', 'Operator')
                ->where('status', 'Active')
                ->first();

            if (!$employee) {
                return redirect()->route('operator.dashboard')
                    ->with('error', 'No active operator account found.');
            }

            // Verify project is assigned to this operator
            if ($project->operator_id !== $employee->id) {
                return redirect()->route('operator.dashboard')
                    ->with('error', 'You are not assigned to this project.');
            }

            // Find the last time log to update
            $lastTimeLog = $project->time_logs()
                ->latest()
                ->first();

            // Update the existing time log with resume time
            if ($lastTimeLog) {
                $lastTimeLog->update([
                    'resume_time' => now(), // Store the current time as resume_time
                    'status' => 'in_progress' // Update status to in_progress
                ]);
            }

            // Update project status
            $project->update(['status' => 'in_progress']);

            return redirect()->route('operator.dashboard')
                ->with('success', 'Project work resumed successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error resuming project work: ' . $e->getMessage());
            return redirect()->route('operator.dashboard')
                ->with('error', 'Failed to resume project work. Please try again.');
        }
    }

    /**
     * Hold project work with meter reading image
     */
    public function holdProjectWork(Request $request, Project $project)
    {
        try {
            $request->validate([
                'meter_reading_image' => 'required|image|max:5120', // Max 5MB
            ]);

            // Get the operator employee
            $employee = Employee::where('role', 'Operator')
                ->where('status', 'Active')
                ->first();

            if (!$employee) {
                return redirect()->route('operator.dashboard')
                    ->with('error', 'No active operator account found.');
            }

            // Verify project is assigned to this operator
            if ($project->operator_id !== $employee->id) {
                return redirect()->route('operator.dashboard')
                    ->with('error', 'You are not assigned to this project.');
            }

            // Store the meter reading image
            $imagePath = null;
            if ($request->hasFile('meter_reading_image')) {
                $file = $request->file('meter_reading_image');
                $fileName = 'hold_' . time() . '_' . $project->id . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('meter-readings', $fileName, 'public');
            }

            if (!$imagePath) {
                throw new \Exception('Failed to save meter reading image.');
            }

            // Find the latest active time log
            $timeLog = $project->time_logs()
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if ($timeLog) {
                $holdTime = now();
                $startTime = $timeLog->start_time;

                // Calculate total working time considering hold and resume times
                $totalMinutes = 0;
                if ($timeLog->hold_time && $timeLog->resume_time) {
                    // Calculate time from start to hold
                    $firstPeriod = $startTime->diffInMinutes($timeLog->hold_time);
                    
                    // Calculate time from resume to end
                    $secondPeriod = $timeLog->resume_time->diffInMinutes($holdTime);
                    
                    $totalMinutes = $firstPeriod + $secondPeriod;
                    
                    // Log the periods for debugging
                    \Log::info('Time periods:', [
                        'first_period' => $firstPeriod,
                        'second_period' => $secondPeriod
                    ]);
                } else if ($timeLog->hold_time) {
                    // If on hold but not resumed, only count time until hold
                    $totalMinutes = $startTime->diffInMinutes($timeLog->hold_time);
                } else {
                    // If no hold time, calculate from start to end
                    $totalMinutes = $startTime->diffInMinutes($holdTime);
                }

                // Log time calculations for debugging
                \Log::info('Time calculations:', [
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'hold_time' => $timeLog->hold_time ? $timeLog->hold_time->format('Y-m-d H:i:s') : null,
                    'resume_time' => $timeLog->resume_time ? $timeLog->resume_time->format('Y-m-d H:i:s') : null,
                    'end_time' => $holdTime->format('Y-m-d H:i:s'),
                    'total_minutes' => $totalMinutes
                ]);

                if ($totalMinutes < 0) {
                    \Log::error('Negative time calculation detected', [
                        'total_minutes' => $totalMinutes,
                        'start_time' => $startTime->format('Y-m-d H:i:s'),
                        'hold_time' => $timeLog->hold_time ? $timeLog->hold_time->format('Y-m-d H:i:s') : null,
                        'resume_time' => $timeLog->resume_time ? $timeLog->resume_time->format('Y-m-d H:i:s') : null,
                        'end_time' => $holdTime->format('Y-m-d H:i:s')
                    ]);
                    throw new \Exception('Invalid time calculation. Please try again.');
                }

                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $hoursDecimal = $hours + ($minutes / 60);

                // Calculate revenue for this session
                $sessionRevenue = 0;
                if ($hoursDecimal < 1) {
                    // If less than an hour, charge fixed price
                    $sessionRevenue = $project->hourly_rate;
                } else {
                    // If more than an hour, calculate based on hours
                    $sessionRevenue = $hoursDecimal * $project->hourly_rate;
                }

                $timeLog->update([
                    'hold_time' => $holdTime,
                    'meter_reading_hold_image' => $imagePath,
                    'total_hours' => $hoursDecimal,
                    'revenue' => $sessionRevenue,
                    'status' => 'on_hold'
                ]);

                // Update project totals from all time logs
                $project->updateTotalsFromLogs();

                // Log after update to verify
                \Log::info('Time log updated:', [
                    'id' => $timeLog->id,
                    'hold_time' => $timeLog->fresh()->hold_time,
                    'meter_reading_hold_image' => $timeLog->fresh()->meter_reading_hold_image
                ]);

            }

            // Update project status
            $project->update(['status' => 'on_hold']);

            return redirect()->route('operator.dashboard')
                ->with('success', 'Project work is now on hold.');
                
        } catch (\Exception $e) {
            \Log::error('Error holding project work: ' . $e->getMessage());
            return redirect()->route('operator.dashboard')
                ->with('error', 'Failed to hold project work. Please try again.');
        }
    }

    /**
     * Stop project work with meter reading image and calculate revenue
     */
    public function stopProjectWork(Request $request, Project $project)
    {
        try {
            $request->validate([
                'meter_reading_image' => 'required|image|max:5120', // Max 5MB
            ]);

            // Get the operator employee
            $employee = Employee::where('role', 'Operator')
                ->where('status', 'Active')
                ->first();

            if (!$employee) {
                return redirect()->route('operator.dashboard')
                    ->with('error', 'No active operator account found.');
            }

            // Verify project is assigned to this operator
            if ($project->operator_id !== $employee->id) {
                return redirect()->route('operator.dashboard')
                    ->with('error', 'You are not assigned to this project.');
            }

            // Store the meter reading image
            $imagePath = null;
            if ($request->hasFile('meter_reading_image')) {
                $file = $request->file('meter_reading_image');
                $fileName = 'end_' . time() . '_' . $project->id . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('meter-readings', $fileName, 'public');
            }

            if (!$imagePath) {
                throw new \Exception('Failed to save meter reading image.');
            }

            // Find the latest time log
            $timeLog = $project->time_logs()
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if ($timeLog) {
                $endTime = now();
                $startTime = $timeLog->start_time;
                $holdTime = $timeLog->hold_time;
                $resumeTime = $timeLog->resume_time;

                // Calculate total working time considering hold and resume times
                $totalMinutes = 0;
                if ($holdTime && $resumeTime) {
                    // Calculate time from start to hold
                    $firstPeriod = $startTime->diffInMinutes($holdTime);
                    
                    // Calculate time from resume to end
                    $secondPeriod = $resumeTime->diffInMinutes($endTime);
                    
                    $totalMinutes = $firstPeriod + $secondPeriod;
                    
                    // Log the periods for debugging
                    \Log::info('Time periods:', [
                        'first_period' => $firstPeriod,
                        'second_period' => $secondPeriod
                    ]);
                } else if ($holdTime) {
                    // If on hold but not resumed, only count time until hold
                    $totalMinutes = $startTime->diffInMinutes($holdTime);
                } else {
                    // If no hold time, calculate from start to end
                    $totalMinutes = $startTime->diffInMinutes($endTime);
                }

                // Log time calculations for debugging
                \Log::info('Time calculations:', [
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'hold_time' => $holdTime ? $holdTime->format('Y-m-d H:i:s') : null,
                    'resume_time' => $resumeTime ? $resumeTime->format('Y-m-d H:i:s') : null,
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'total_minutes' => $totalMinutes
                ]);

                if ($totalMinutes < 0) {
                    \Log::error('Negative time calculation detected', [
                        'total_minutes' => $totalMinutes,
                        'start_time' => $startTime->format('Y-m-d H:i:s'),
                        'hold_time' => $holdTime ? $holdTime->format('Y-m-d H:i:s') : null,
                        'resume_time' => $resumeTime ? $resumeTime->format('Y-m-d H:i:s') : null,
                        'end_time' => $endTime->format('Y-m-d H:i:s')
                    ]);
                    throw new \Exception('Invalid time calculation. Please try again.');
                }

                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $hoursDecimal = $hours + ($minutes / 60);

                // Calculate revenue for this session
                $sessionRevenue = 0;
                if ($hoursDecimal < 1) {
                    // If less than an hour, charge fixed price
                    $sessionRevenue = $project->hourly_rate;
                } else {
                    // If more than an hour, calculate based on hours
                    $sessionRevenue = $hoursDecimal * $project->hourly_rate;
                }

                $timeLog->update([
                    'end_time' => $endTime,
                    'meter_reading_end_image' => $imagePath,
                    'total_hours' => $hoursDecimal,
                    'revenue' => $sessionRevenue,
                    'status' => 'completed'
                ]);

                // Update project totals from all time logs
                $project->updateTotalsFromLogs();
            }

            // Update project status
            $project->update(['status' => 'completed']);

            return redirect()->route('operator.dashboard')
                ->with('success', 'Project work completed successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error stopping project work: ' . $e->getMessage());
            return redirect()->route('operator.dashboard')
                ->with('error', 'Failed to stop project work. Please try again.');
        }
    }

    /**
     * Display the operator dashboard with assigned projects
     */
    public function operatorDashboard()
    {
        // Get the authenticated employee
        $employee = Employee::where('role', 'Operator')
            ->where('status', 'Active')
            ->first();

        if (!$employee) {
            return redirect()->route('login')
                ->with('error', 'No active operator account found.');
        }
        
        // Get projects assigned to the operator
        $assignedProjects = Project::with(['client', 'machine', 'attachments', 'time_logs' => function($query) {
            $query->latest();
        }])
        ->where('operator_id', $employee->id)
        ->orderByRaw("FIELD(status, 'in_progress', 'on_hold', 'created', 'completed')")
        ->get();

        return view('operator.dashboard', compact('assignedProjects'));
    }

    /**
     * Download a project attachment
     */
    public function downloadAttachment(ProjectAttachment $attachment)
    {
        // Check if file exists
        if (!Storage::exists($attachment->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::download($attachment->file_path, $attachment->original_name);
    }

    /**
     * Get the worked time for a project
     */
    public function getWorkedTime(Project $project)
    {
        $currentTimeLog = $project->currentTimeLog;
        $response = ['success' => true];

        if ($currentTimeLog && $currentTimeLog->status === 'in_progress') {
            $startTime = $currentTimeLog->start_time;
            $totalMinutes = 0;

            if ($currentTimeLog->hold_time) {
                // Add time from start to hold
                $totalMinutes += $startTime->diffInMinutes($currentTimeLog->hold_time);
                
                // Add time from resume to now if resumed
                if ($currentTimeLog->resume_time) {
                    $totalMinutes += $currentTimeLog->resume_time->diffInMinutes(now());
                }
            } else {
                // No hold time, calculate from start to now
                $totalMinutes += $startTime->diffInMinutes(now());
            }

            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            
            $response['worked_time'] = sprintf('%02d:%02d', $hours, $minutes);
            $response['total_minutes'] = $totalMinutes;
        } else {
            $response['worked_time'] = '00:00';
            $response['total_minutes'] = 0;
        }

        return response()->json($response);
    }
}
