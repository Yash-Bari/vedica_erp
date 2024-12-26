<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines');
            $table->foreignId('employee_id')->constrained('employees'); // Who performed the check
            $table->date('check_date');
            $table->time('check_time');
            $table->enum('overall_condition', ['Excellent', 'Good', 'Fair', 'Poor', 'Critical'])->default('Good');
            $table->decimal('engine_temperature', 8, 2)->nullable();
            $table->decimal('oil_pressure', 8, 2)->nullable();
            $table->decimal('fuel_level', 5, 2)->nullable();
            $table->boolean('hydraulic_system_check')->default(false);
            $table->boolean('electrical_system_check')->default(false);
            $table->boolean('tire_condition_check')->default(false);
            $table->text('engine_remarks')->nullable();
            $table->text('hydraulic_remarks')->nullable();
            $table->text('electrical_remarks')->nullable();
            $table->text('tire_remarks')->nullable();
            $table->string('health_check_image')->nullable(); // Store image path
            $table->string('voice_note')->nullable(); // Store voice note path
            $table->enum('maintenance_recommendation', ['None', 'Minor Repair', 'Major Repair', 'Immediate Service'])->default('None');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_health_checks');
    }
};
