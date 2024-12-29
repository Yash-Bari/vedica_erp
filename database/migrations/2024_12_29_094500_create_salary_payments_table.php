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
        if (!Schema::hasTable('salary_payments')) {
            Schema::create('salary_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->onDelete('restrict');
                $table->foreignId('salary_structure_id')->constrained()->onDelete('restrict');
                $table->integer('year');
                $table->integer('month');
                $table->decimal('basic_salary', 10, 2);
                $table->json('allowances')->nullable();
                $table->json('deductions')->nullable();
                $table->decimal('net_salary', 10, 2);
                $table->timestamp('payment_date');
                $table->string('status')->default('pending');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
