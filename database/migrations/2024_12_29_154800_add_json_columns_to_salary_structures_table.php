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
        Schema::table('salary_structures', function (Blueprint $table) {
            // Drop existing columns if they exist
            if (Schema::hasColumn('salary_structures', 'house_rent_allowance')) {
                $table->dropColumn([
                    'house_rent_allowance',
                    'conveyance_allowance',
                    'medical_allowance',
                    'performance_bonus',
                    'provident_fund',
                    'professional_tax',
                    'other_deductions'
                ]);
            }

            // Add new JSON columns
            $table->json('allowances')->nullable()->after('bonus_percentage');
            $table->json('deductions')->nullable()->after('allowances');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            // Drop JSON columns
            $table->dropColumn(['allowances', 'deductions']);

            // Restore original columns
            $table->decimal('house_rent_allowance', 10, 2)->default(0);
            $table->decimal('conveyance_allowance', 10, 2)->default(0);
            $table->decimal('medical_allowance', 10, 2)->default(0);
            $table->decimal('performance_bonus', 10, 2)->default(0);
            $table->decimal('provident_fund', 10, 2)->default(0);
            $table->decimal('professional_tax', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
        });
    }
};
