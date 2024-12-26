<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->decimal('base_salary', 15, 2);
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('overtime_rate', 10, 2);
            $table->decimal('bonus_percentage', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->year('year');
            $table->enum('month', [
                'January', 'February', 'March', 'April', 
                'May', 'June', 'July', 'August', 
                'September', 'October', 'November', 'December'
            ]);
            $table->decimal('base_salary', 15, 2);
            $table->decimal('overtime_hours', 10, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('total_earnings', 15, 2);
            $table->decimal('tax_deduction', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->enum('status', [
                'Pending', 
                'Processed', 
                'Paid', 
                'Failed'
            ])->default('Pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->string('deduction_type');
            $table->decimal('amount', 15, 2);
            $table->date('effective_date');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_deductions');
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('salary_structures');
    }
};
