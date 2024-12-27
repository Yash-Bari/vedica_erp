<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quotation_machine_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('machine_id');
            $table->decimal('rate_per_hour', 10, 2);
            $table->decimal('estimated_hours', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        Schema::table('quotation_machine_details', function (Blueprint $table) {
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotation_machine_details');
    }
};
