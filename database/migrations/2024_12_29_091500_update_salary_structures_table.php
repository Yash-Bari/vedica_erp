<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            // Add allowances
            $table->decimal('house_rent_allowance', 15, 2)->default(0)->after('base_salary');
            $table->decimal('conveyance_allowance', 15, 2)->default(0)->after('house_rent_allowance');
            $table->decimal('medical_allowance', 15, 2)->default(0)->after('conveyance_allowance');
            $table->decimal('performance_bonus', 15, 2)->default(0)->after('medical_allowance');
            
            // Add deductions
            $table->decimal('provident_fund', 15, 2)->default(0)->after('performance_bonus');
            $table->decimal('professional_tax', 15, 2)->default(0)->after('provident_fund');
            $table->decimal('other_deductions', 15, 2)->default(0)->after('professional_tax');
            
            // Add net salary percentage
            $table->decimal('net_salary_percentage', 5, 2)->default(100)->after('other_deductions');
        });
    }

    public function down(): void
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->dropColumn([
                'house_rent_allowance',
                'conveyance_allowance',
                'medical_allowance',
                'performance_bonus',
                'provident_fund',
                'professional_tax',
                'other_deductions',
                'net_salary_percentage'
            ]);
        });
    }
};
