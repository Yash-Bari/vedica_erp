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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            // Rename phone_number to phone for consistency
            $table->renameColumn('phone_number', 'phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('employee_code');
            $table->renameColumn('phone', 'phone_number');
        });
    }
};
