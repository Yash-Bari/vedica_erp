<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Machine::class);

        $query = Machine::query();

        // Filtering
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Sorting
        $query->orderBy(
            $request->get('sort_by', 'created_at'), 
            $request->get('sort_direction', 'desc')
        );

        $machines = $query->paginate(15);
        $projects = Project::all();

        return view('machines.index', compact('machines', 'projects'));
    }

    public function create()
    {
        $this->authorize('create', Machine::class);

        $projects = Project::all();
        return view('machines.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Machine::class);

        $validatedData = $request->validate([
            'name' => 'required|unique:machines,name|max:255',
            'model_number' => 'nullable|max:100',
            'serial_number' => 'nullable|unique:machines,serial_number|max:100',
            'type' => ['required', Rule::in(array_keys(Machine::TYPES))],
            'status' => ['required', Rule::in(array_keys(Machine::STATUS))],
            'project_id' => 'nullable|exists:projects,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date|before_or_equal:today',
            'last_maintenance_date' => 'nullable|date|before_or_equal:today',
            'manufacturer' => 'nullable|max:255',
            'year_of_manufacture' => 'nullable|integer|min:1900|max:' . now()->year,
            'operating_weight' => 'nullable|numeric|min:0',
            'fuel_capacity' => 'nullable|numeric|min:0',
            'current_location' => 'nullable|max:500',
            'notes' => 'nullable|max:1000'
        ]);

        $machine = Machine::create($validatedData);

        return redirect()->route('machines.show', $machine)
            ->with('success', 'Machine created successfully');
    }

    public function show(Machine $machine)
    {
        $this->authorize('view', $machine);

        $machine->load([
            'project', 
            'healthChecks', 
            'maintenances', 
            'expenses'
        ]);

        return view('machines.show', compact('machine'));
    }

    public function edit(Machine $machine)
    {
        $this->authorize('update', $machine);

        $projects = Project::all();
        return view('machines.edit', compact('machine', 'projects'));
    }

    public function update(Request $request, Machine $machine)
    {
        $this->authorize('update', $machine);

        $validatedData = $request->validate([
            'name' => [
                'required', 
                'max:255', 
                Rule::unique('machines')->ignore($machine->id)
            ],
            'model_number' => 'nullable|max:100',
            'serial_number' => [
                'nullable', 
                'max:100', 
                Rule::unique('machines')->ignore($machine->id)
            ],
            'type' => ['required', Rule::in(array_keys(Machine::TYPES))],
            'status' => ['required', Rule::in(array_keys(Machine::STATUS))],
            'project_id' => 'nullable|exists:projects,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date|before_or_equal:today',
            'last_maintenance_date' => 'nullable|date|before_or_equal:today',
            'manufacturer' => 'nullable|max:255',
            'year_of_manufacture' => 'nullable|integer|min:1900|max:' . now()->year,
            'operating_weight' => 'nullable|numeric|min:0',
            'fuel_capacity' => 'nullable|numeric|min:0',
            'current_location' => 'nullable|max:500',
            'notes' => 'nullable|max:1000'
        ]);

        $machine->update($validatedData);

        return redirect()->route('machines.show', $machine)
            ->with('success', 'Machine updated successfully');
    }

    public function destroy(Machine $machine)
    {
        $this->authorize('delete', $machine);

        $machine->delete();

        return redirect()->route('machines.index')
            ->with('success', 'Machine deleted successfully');
    }

    public function maintenance(Request $request)
    {
        $this->authorize('generateReports', Machine::class);

        $query = Machine::query();

        // Maintenance Status Filtering
        $query->where(function($q) {
            $q->where('status', Machine::STATUS['Maintenance'])
              ->orWhere('status', Machine::STATUS['Repair']);
        });

        $machines = $query->paginate(15);

        return view('machines.maintenance', compact('machines'));
    }

    public function export(Request $request)
    {
        $this->authorize('export', Machine::class);

        $query = Machine::query();

        // Optional Filtering
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $machines = $query->get();

        // Generate CSV
        $filename = 'machines_' . now()->format('YmdHis') . '.csv';
        $handle = fopen($filename, 'w');

        // CSV headers
        fputcsv($handle, [
            'ID', 'Name', 'Type', 'Status', 
            'Project', 'Purchase Date', 'Manufacturer', 
            'Total Maintenance Cost'
        ]);

        // CSV rows
        foreach ($machines as $machine) {
            fputcsv($handle, [
                $machine->id,
                $machine->name,
                $machine->type,
                $machine->status,
                $machine->project ? $machine->project->name : 'N/A',
                $machine->purchase_date ? $machine->purchase_date->format('Y-m-d') : 'N/A',
                $machine->manufacturer,
                $machine->getTotalMaintenanceCost()
            ]);
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }
}
