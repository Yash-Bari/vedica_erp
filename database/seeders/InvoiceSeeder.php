<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have a clean slate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Invoice::truncate();
        InvoiceItem::truncate();
        Payment::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get a completed project
        $project = Project::where('status', 'Completed')->first();

        if (!$project) {
            // If no completed project exists, create one
            $project = Project::factory()->completed()->create();
        }

        // Create an invoice for this project
        $invoice = Invoice::create([
            'project_id' => $project->id,
            'client_id' => $project->client_id,
            'created_by' => 1, // Assuming first user
            'invoice_number' => 'INV-2024-0001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $project->total_revenue,
            'gst_amount' => $project->total_revenue * 0.18,
            'total_amount' => $project->total_revenue * 1.18,
            'status' => 'Pending',
            'notes' => 'Invoice for completed project'
        ]);

        // Create invoice items
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Project Services',
            'quantity' => 1,
            'unit_price' => $project->total_revenue,
            'total_price' => $project->total_revenue
        ]);

        // Optionally create a payment
        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total_amount,
            'payment_date' => now(),
            'payment_method' => 'Bank Transfer',
            'transaction_reference' => 'TRX-' . uniqid(),
            'notes' => 'Full payment received'
        ]);
    }
}
