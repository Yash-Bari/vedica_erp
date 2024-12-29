<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Create a backup of the existing table
        if (Schema::hasTable('salary_payments') && !Schema::hasTable('salary_payments_backup')) {
            DB::statement('CREATE TABLE salary_payments_backup LIKE salary_payments');
            DB::statement('INSERT INTO salary_payments_backup SELECT * FROM salary_payments');
        }

        // Step 2: Drop foreign keys referencing salary_payments
        Schema::table('salary_receipts', function (Blueprint $table) {
            $table->dropForeign(['salary_payment_id']);
        });

        // Step 3: Drop the existing table
        Schema::dropIfExists('salary_payments');

        // Step 4: Create the table with the new structure
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('restrict');
            $table->foreignId('salary_structure_id')->constrained()->onDelete('restrict');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('basic_salary', 10, 2);
            $table->json('allowances')->nullable();
            $table->json('deductions')->nullable();
            $table->decimal('net_salary', 10, 2);
            $table->timestamp('payment_date');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // Step 5: Restore foreign key on salary_receipts
        Schema::table('salary_receipts', function (Blueprint $table) {
            $table->foreign('salary_payment_id')
                ->references('id')
                ->on('salary_payments')
                ->onDelete('cascade');
        });

        // Step 6: Migrate data from backup if it exists
        if (Schema::hasTable('salary_payments_backup')) {
            $backupData = DB::table('salary_payments_backup')->get();
            foreach ($backupData as $payment) {
                // Get the active salary structure for the employee
                $salaryStructure = DB::table('salary_structures')
                    ->where('employee_id', $payment->employee_id)
                    ->where('is_active', 1)
                    ->first();

                if ($salaryStructure) {
                    DB::table('salary_payments')->insert([
                        'id' => $payment->id,
                        'employee_id' => $payment->employee_id,
                        'salary_structure_id' => $salaryStructure->id,
                        'year' => $payment->year,
                        'month' => $payment->month,
                        'basic_salary' => $payment->basic_salary,
                        'allowances' => is_string($payment->allowances) ? $payment->allowances : json_encode(['amount' => $payment->allowances]),
                        'deductions' => is_string($payment->deductions) ? $payment->deductions : json_encode(['amount' => $payment->deductions]),
                        'net_salary' => $payment->net_salary,
                        'payment_date' => $payment->payment_date,
                        'status' => $payment->status,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                        'deleted_at' => $payment->deleted_at ?? null
                    ]);
                }
            }

            // Step 7: Drop the backup table
            Schema::dropIfExists('salary_payments_backup');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First drop the foreign key
        Schema::table('salary_receipts', function (Blueprint $table) {
            $table->dropForeign(['salary_payment_id']);
        });

        // Then drop the table
        Schema::dropIfExists('salary_payments');

        // Restore the backup if it exists
        if (Schema::hasTable('salary_payments_backup')) {
            DB::statement('CREATE TABLE salary_payments LIKE salary_payments_backup');
            DB::statement('INSERT INTO salary_payments SELECT * FROM salary_payments_backup');
            Schema::dropIfExists('salary_payments_backup');
        }

        // Restore the foreign key
        Schema::table('salary_receipts', function (Blueprint $table) {
            $table->foreign('salary_payment_id')
                ->references('id')
                ->on('salary_payments')
                ->onDelete('cascade');
        });
    }
};
