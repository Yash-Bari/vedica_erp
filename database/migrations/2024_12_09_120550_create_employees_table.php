<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            
            // Profile and Documents
            $table->string('profile_picture')->nullable();
            $table->string('aadhaar_card')->nullable();
            $table->string('driving_license')->nullable();
            
            // Address Information
            $table->text('permanent_address')->nullable();
            $table->text('current_address')->nullable();
            
            // Employment Details
            $table->enum('role', [
                'Admin', 
                'Marketing', 
                'Supervisor', 
                'Manager', 
                'Finance', 
                'Helper', 
                'Operator'
            ]);
            $table->enum('status', ['Active', 'Inactive', 'Suspended'])->default('Active');
            
            // Bank Details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc_code')->nullable();
            
            // Authentication
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamp('last_login_at')->nullable();
            
            // Soft Delete and Timestamps
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
