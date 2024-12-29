<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('bonus_percentage');
        });

        // Set the latest salary structure for each employee as active
        DB::statement("
            UPDATE salary_structures s1
            LEFT JOIN (
                SELECT employee_id, MAX(created_at) as latest_date
                FROM salary_structures
                GROUP BY employee_id
            ) s2 ON s1.employee_id = s2.employee_id AND s1.created_at = s2.latest_date
            SET s1.is_active = (s2.latest_date IS NOT NULL)
        ");
    }

    public function down(): void
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
