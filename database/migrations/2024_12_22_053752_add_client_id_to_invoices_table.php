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
        Schema::table('invoices', function (Blueprint $table) {
            // Add client_id if it doesn't exist
            if (!Schema::hasColumn('invoices', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable();
                
                // Add foreign key constraint
                $table->foreign('client_id')
                    ->references('id')
                    ->on('clients')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key and column if exists
            if (Schema::hasColumn('invoices', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
        });
    }
};
