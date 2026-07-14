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
        Schema::create('snelstart_exports', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->date('export_date');
            $table->decimal('total_price', 10, 2);
            $table->decimal('total_price_per_week', 10, 2);
            $table->integer('booking_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snelstart_exports');
    }
};
