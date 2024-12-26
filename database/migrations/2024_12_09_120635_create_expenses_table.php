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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('machine_id')->nullable()->constrained()->onDelete('set null');

            // Expense Details
            $table->decimal('amount', 12, 2);
            $table->date('date');
            
            // Expense Categorization
            $table->enum('type', [
                'Material', 
                'Labor', 
                'Equipment', 
                'Transportation', 
                'Miscellaneous'
            ]);
            $table->enum('category', ['Direct', 'Indirect'])->default('Direct');
            
            // Additional Details
            $table->text('description')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->enum('payment_method', [
                'Cash', 
                'Bank Transfer', 
                'Credit Card', 
                'Debit Card', 
                'Cheque'
            ])->nullable();

            // Soft Delete and Timestamps
            $table->softDeletes();
            $table->timestamps();

            // Unique constraints
            $table->unique(['invoice_number', 'vendor_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
