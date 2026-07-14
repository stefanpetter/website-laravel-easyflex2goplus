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
        Schema::create('flexworkers', function (Blueprint $table) {
            $table->id();
            $table->integer('relation_id')->nullable();
            $table->integer('snelstart_id')->nullable();
            $table->string('status');
            $table->string('invoice');
            $table->string('initials');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('gender');
            $table->string('nationality');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flexworkers');
    }
};
