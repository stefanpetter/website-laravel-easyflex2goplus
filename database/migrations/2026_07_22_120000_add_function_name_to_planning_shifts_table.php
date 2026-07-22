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
        Schema::table('planning_shifts', function (Blueprint $table) {
            $table->string('function_name')->nullable()->after('role_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_shifts', function (Blueprint $table) {
            $table->dropColumn('function_name');
        });
    }
};
