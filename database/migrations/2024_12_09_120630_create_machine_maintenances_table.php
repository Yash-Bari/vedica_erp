<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines');
            $table->foreignId('employee_id')->constrained('employees'); // Who initiated maintenance
            $table->string('maintenance_type');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Low');
            $table->date('scheduled_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->enum('status', ['Scheduled', 'In Progress', 'Completed', 'Canceled'])->default('Scheduled');
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->text('technician_notes')->nullable();
            $table->string('before_maintenance_image')->nullable();
            $table->string('after_maintenance_image')->nullable();
            $table->boolean('warranty_claim')->default(false);
            $table->text('warranty_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_maintenances');
    }
};
