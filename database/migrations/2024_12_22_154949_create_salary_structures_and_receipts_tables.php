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
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('house_rent_allowance', 10, 2)->nullable();
            $table->decimal('conveyance_allowance', 10, 2)->nullable();
            $table->decimal('medical_allowance', 10, 2)->nullable();
            $table->decimal('performance_bonus', 10, 2)->nullable();
            $table->decimal('provident_fund', 10, 2)->nullable();
            $table->decimal('professional_tax', 10, 2)->nullable();
            $table->decimal('other_deductions', 10, 2)->nullable();
            $table->float('net_salary_percentage')->default(100);
            $table->boolean('is_active')->default(true);
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            
            $table->timestamps();
        });

        Schema::create('salary_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_payment_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('receipt_number')->unique();
            $table->decimal('total_earnings', 10, 2);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->text('remarks')->nullable();
            
            $table->foreign('salary_payment_id')->references('id')->on('salary_payments')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_receipts');
        Schema::dropIfExists('salary_structures');
    }
};
