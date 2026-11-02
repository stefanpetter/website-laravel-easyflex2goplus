<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_shift_assignments', function (Blueprint $table) {
            $table->string('absence_status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('planning_shift_assignments', function (Blueprint $table) {
            $table->dropColumn('absence_status');
        });
    }
};