<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        // If the table doesn't exist, create it
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->string('phone')->unique();
                $table->string('email')->nullable()->unique();
                $table->text('address')->nullable();
                $table->enum('source', ['IndiaMart', 'Justdial', 'TIC', 'Other'])->default('Other');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
