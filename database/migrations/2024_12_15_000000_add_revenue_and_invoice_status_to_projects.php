<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRevenueAndInvoiceStatusToProjects extends Migration
{
    public function up()
    {
        // First add the columns
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'revenue')) {
                $table->decimal('revenue', 12, 2)->default(0)->after('status');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'invoice_status')) {
                $table->string('invoice_status')->default('Pending')->after('revenue');
            }
        });

        // Then update the data
        if (Schema::hasColumn('projects', 'total_revenue') && Schema::hasColumn('projects', 'revenue')) {
            DB::statement('UPDATE projects SET revenue = total_revenue WHERE revenue = 0 AND total_revenue > 0');
        }

        // Update status to proper case if needed
        DB::statement("UPDATE projects SET status = 'Completed' WHERE status = 'completed'");
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'invoice_status')) {
                $table->dropColumn('invoice_status');
            }
            if (Schema::hasColumn('projects', 'revenue')) {
                $table->dropColumn('revenue');
            }
        });
    }
}
