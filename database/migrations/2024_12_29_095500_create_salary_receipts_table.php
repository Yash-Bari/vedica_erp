<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_payment_id')->constrained('salary_payments')->onDelete('cascade');
            $table->string('receipt_number')->unique();
            $table->json('salary_details')->nullable(); // Store a snapshot of salary details
            $table->json('payment_details')->nullable(); // Store payment transaction details
            $table->string('generated_by')->nullable(); // User who generated the receipt
            $table->timestamp('generated_at')->nullable();
            $table->string('pdf_path')->nullable(); // Path to stored PDF receipt
            $table->string('status')->default('pending'); // Add status field with default value
            $table->timestamps();
            
            // Add index for faster lookups
            $table->index(['receipt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_receipts');
    }
};
