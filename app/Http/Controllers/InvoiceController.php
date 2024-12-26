<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['project', 'client', 'creator']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('invoice_date', [
                $request->start_date, 
                $request->end_date
            ]);
        }

        $invoices = $query->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        // First, let's update any completed projects that might have lowercase status
        Project::where('status', 'completed')
            ->update(['status' => Project::STATUS_COMPLETED]);

        $projects = Project::where('status', Project::STATUS_COMPLETED)
            ->orWhere('status', 'completed')  // Check both cases just in case
            ->whereDoesntHave('invoice')
            ->where(function($query) {
                $query->where('revenue', '>', 0)
                      ->orWhere('total_revenue', '>', 0);  // Check both revenue fields
            })
            ->get();

        // Debug information
        Log::info('Projects Query:', [
            'count' => $projects->count(),
            'sql' => Project::where('status', Project::STATUS_COMPLETED)
                ->orWhere('status', 'completed')
                ->whereDoesntHave('invoice')
                ->where(function($query) {
                    $query->where('revenue', '>', 0)
                          ->orWhere('total_revenue', '>', 0);
                })
                ->toSql(),
            'projects' => $projects->toArray()
        ]);

        // Let's also get all projects to see what we have
        $allProjects = Project::all();
        Log::info('All Projects:', [
            'count' => $allProjects->count(),
            'projects' => $allProjects->toArray()
        ]);

        return view('invoices.create', compact('projects'));
    }

    public function fetchProjectDetails(Project $project)
    {
        if (strtolower($project->status) !== strtolower(Project::STATUS_COMPLETED)) {
            return response()->json(['error' => 'Project must be completed'], 400);
        }

        $invoice = new Invoice();
        $amounts = $invoice->calculateAmounts($project->total_revenue ?: $project->revenue);
        
        return response()->json([
            'project' => $project,
            'amounts' => $amounts
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after:invoice_date',
            'notes' => 'nullable|string',
        ]);

        // Fetch the project
        $project = Project::findOrFail($request->project_id);

        // Create the invoice
        $invoice = new Invoice([
            'project_id' => $project->id,
            'client_id' => $project->client_id, 
            'created_by' => auth()->id(),
            'invoice_date' => $validatedData['invoice_date'],
            'due_date' => $validatedData['due_date'],
            'notes' => $validatedData['notes'] ?? null,
            'subtotal' => $project->total_revenue ?? 0,
            'gst_amount' => ($project->total_revenue ?? 0) * 0.18,
            'total_amount' => ($project->total_revenue ?? 0) * 1.18,
            'status' => 'Pending'
        ]);

        // Generate invoice number
        $invoice->invoice_number = $invoice->generateInvoiceNumber();

        // Save the invoice
        $invoice->save();

        // Update project invoice status
        $project->update([
            'invoice_status' => 'Invoiced'
        ]);

        return redirect()->route('invoices.index')
            ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['project', 'client', 'items', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function sendInvoice(Invoice $invoice)
    {
        // Update invoice status
        $invoice->update(['status' => 'Sent']);

        // TODO: Implement email sending logic
        // EmailService::sendInvoiceToClient($invoice);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice sent to client');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0|max:' . ($invoice->net_amount - $invoice->total_paid),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Bank Transfer,Cheque,Online Payment',
            'transaction_reference' => 'nullable|string',
            'notes' => 'nullable|string|max:500'
        ]);

        $payment = $invoice->payments()->create($validatedData);

        // Check if invoice is fully paid
        if ($invoice->fresh()->isFullyPaid()) {
            $invoice->markAsPaid();
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Payment recorded successfully');
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonths(3));
        $endDate = $request->input('end_date', now());

        $totalRevenue = Invoice::getTotalRevenue($startDate, $endDate);
        $invoiceCount = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->count();
        $averageInvoiceValue = $invoiceCount > 0 ? $totalRevenue / $invoiceCount : 0;

        return view('invoices.report', [
            'totalRevenue' => $totalRevenue,
            'invoiceCount' => $invoiceCount,
            'averageInvoiceValue' => $averageInvoiceValue,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
