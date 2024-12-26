<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'rates' => 'required|array',
            'rates.*' => 'required|numeric|min:0',
            'hours' => 'required|array',
            'hours.*' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $project = Project::with(['client', 'machines'])->findOrFail($request->project_id);
        
        // Calculate total amount
        $totalAmount = 0;
        foreach ($project->machines as $machine) {
            $rate = $request->rates[$machine->id];
            $hours = $request->hours[$machine->id];
            $totalAmount += $rate * $hours;
        }

        // Create quotation
        $quotation = Quotation::create([
            'project_id' => $project->id,
            'client_id' => $project->client_id,
            'total_amount' => $totalAmount,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
            'status' => 'Pending'
        ]);

        // Store machine details
        foreach ($project->machines as $machine) {
            $quotation->machineDetails()->create([
                'machine_id' => $machine->id,
                'rate_per_hour' => $request->rates[$machine->id],
                'estimated_hours' => $request->hours[$machine->id],
                'total' => $request->rates[$machine->id] * $request->hours[$machine->id]
            ]);
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Quotation generated successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['project', 'client', 'machineDetails.machine']);
        return view('quotations.show', compact('quotation'));
    }
}
