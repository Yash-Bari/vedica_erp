<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineHealthCheck;
use App\Services\MachineHealthCheckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MachineHealthCheckController extends Controller
{
    protected $machineHealthCheckService;

    public function __construct(MachineHealthCheckService $machineHealthCheckService)
    {
        $this->machineHealthCheckService = $machineHealthCheckService;
    }

    /**
     * Show health check form for a specific machine
     */
    public function create(Machine $machine)
    {
        return view('machine-health-checks.create', compact('machine'));
    }

    /**
     * Store a new machine health check
     */
    public function store(Request $request, Machine $machine)
    {
        $validator = Validator::make($request->all(), [
            'check_date' => 'required|date',
            'check_time' => 'required|date_format:H:i',
            'overall_condition' => 'required|in:Excellent,Good,Fair,Poor,Critical',
            'engine_temperature' => 'nullable|numeric',
            'oil_pressure' => 'nullable|numeric',
            'fuel_level' => 'nullable|numeric|min:0|max:100',
            'hydraulic_system_check' => 'nullable',
            'electrical_system_check' => 'nullable',
            'tire_condition_check' => 'nullable',
            'engine_remarks' => 'nullable|string',
            'hydraulic_remarks' => 'nullable|string',
            'electrical_remarks' => 'nullable|string',
            'tire_remarks' => 'nullable|string',
            'health_check_image' => 'nullable|image|max:5120', // 5MB max
            'voice_note' => 'nullable|mimes:mp3,wav,m4a|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $healthCheckData = $validator->validated();
            
            // Convert checkbox values to boolean
            $healthCheckData['hydraulic_system_check'] = $request->has('hydraulic_system_check');
            $healthCheckData['electrical_system_check'] = $request->has('electrical_system_check');
            $healthCheckData['tire_condition_check'] = $request->has('tire_condition_check');
            
            $healthCheckData['machine_id'] = $machine->id;
            $healthCheckData['employee_id'] = auth()->id();

            $healthCheck = $this->machineHealthCheckService->performHealthCheck($healthCheckData);

            return redirect()->route('machines.show', $machine->id)
                ->with('success', 'Machine health check recorded successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to record health check: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * List health checks for a specific machine
     */
    public function index(Machine $machine)
    {
        $healthChecks = $machine->healthChecks()->latest()->paginate(10);
        return view('machine-health-checks.index', compact('machine', 'healthChecks'));
    }

    /**
     * Show details of a specific health check
     */
    public function show(MachineHealthCheck $healthCheck)
    {
        return view('machine-health-checks.show', compact('healthCheck'));
    }

    /**
     * Display a list of all machines for health checks
     */
    public function allMachinesHealthChecks()
    {
        $machines = Machine::where('status', 'Available')
            ->withCount('healthChecks')
            ->orderBy('name')
            ->paginate(10);

        return view('machine-health-checks.all-machines', compact('machines'));
    }
}
