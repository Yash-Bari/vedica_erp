<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTimeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamp('hold_time')->nullable();
            $table->timestamp('resume_time')->nullable();
            $table->decimal('meter_reading_start', 10, 2)->nullable();
            $table->string('meter_reading_start_image')->nullable();
            $table->string('meter_reading_hold_image')->nullable();
            $table->decimal('meter_reading_end', 10, 2)->nullable();
            $table->string('meter_reading_end_image')->nullable();
            $table->decimal('total_hours', 10, 2)->nullable();
            $table->decimal('revenue', 15, 2)->nullable();
            $table->enum('status', ['in_progress', 'on_hold', 'completed'])->default('in_progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_time_logs');
    }
}
