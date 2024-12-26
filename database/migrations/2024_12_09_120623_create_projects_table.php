<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ensure clients table is created first
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->string('phone')->unique();
                $table->string('email')->nullable()->unique();
                $table->text('address')->nullable();
                $table->enum('source', ['IndiaMart', 'Justdial', 'TIC', 'Other'])->default('Other');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Ensure machines table is created first
        if (!Schema::hasTable('machines')) {
            Schema::create('machines', function (Blueprint $table) {
                $table->id();
                
                // Identification Details
                $table->string('name')->unique();
                $table->string('model_number')->nullable();
                $table->string('serial_number')->unique()->nullable();
                
                // Classification
                $table->enum('type', [
                    'Excavator', 
                    'Bulldozer', 
                    'Crane', 
                    'Dump Truck', 
                    'Loader', 
                    'Compactor', 
                    'Grader', 
                    'Roller', 
                    'Backhoe', 
                    'Other'
                ]);
                
                // Status Tracking
                $table->enum('status', [
                    'Active', 
                    'Maintenance', 
                    'Inactive', 
                    'Repair', 
                    'Available', 
                    'In Use'
                ])->default('Available');
                
                // Financial Details
                $table->decimal('purchase_price', 15, 2)->nullable();
                $table->date('purchase_date')->nullable();
                $table->date('last_maintenance_date')->nullable();
                
                // Technical Specifications
                $table->string('manufacturer')->nullable();
                $table->integer('year_of_manufacture')->nullable();
                $table->decimal('operating_weight', 10, 2)->nullable(); // in kg
                $table->decimal('fuel_capacity', 10, 2)->nullable(); // in liters
                
                // Tracking and Maintenance
                $table->text('current_location')->nullable();
                $table->text('notes')->nullable();
                
                // Soft Delete and Timestamps
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // Ensure employees table is created first
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('phone_number')->unique();
                $table->string('username')->unique();
                $table->string('password');
                $table->enum('role', ['Admin', 'Manager', 'Operator', 'Accountant'])->default('Operator');
                $table->enum('status', ['Active', 'Inactive', 'Suspended'])->default('Active');
                $table->date('date_of_birth')->nullable();
                $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
                $table->string('address')->nullable();
                $table->date('joining_date')->nullable();
                $table->date('leaving_date')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create projects table
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['created', 'in_progress', 'on_hold', 'completed'])->default('created');
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('total_hours', 10, 2)->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->string('meter_image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('set null');
        });

        // Create pivot table for project-machine relationship
        Schema::create('project_machines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('machine_id');
            
            // Foreign Key Constraints
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
            
            $table->foreign('machine_id')
                  ->references('id')
                  ->on('machines')
                  ->onDelete('cascade');
            
            // Prevent duplicate entries
            $table->unique(['project_id', 'machine_id']);
            
            $table->timestamps();
        });

        // Create project attachments table
        Schema::create('project_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->string('file_path');
            $table->enum('type', [
                'machine_manual', 
                'safety_document', 
                'site_plan', 
                'equipment_checklist', 
                'insurance', 
                'other'
            ]);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_attachments');
        Schema::dropIfExists('project_machines');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('machines');
        Schema::dropIfExists('clients');
    }
}
