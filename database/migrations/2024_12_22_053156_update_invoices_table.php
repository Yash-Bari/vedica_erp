<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Ensure all required columns exist
            if (!Schema::hasColumn('invoices', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable();
            }
            
            if (!Schema::hasColumn('invoices', 'date')) {
                $table->date('date')->nullable();
            }
            
            if (!Schema::hasColumn('invoices', 'due_date')) {
                $table->date('due_date')->nullable();
            }
            
            if (!Schema::hasColumn('invoices', 'notes')) {
                $table->text('notes')->nullable();
            }
            
            if (!Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('invoices', 'gst_amount')) {
                $table->decimal('gst_amount', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('invoices', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('invoices', 'invoice_number')) {
                $table->string('invoice_number')->unique()->nullable();
            }
            
            if (!Schema::hasColumn('invoices', 'status')) {
                $table->string('status')->default('Pending');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Optional: Remove columns if needed
        });
    }
};
