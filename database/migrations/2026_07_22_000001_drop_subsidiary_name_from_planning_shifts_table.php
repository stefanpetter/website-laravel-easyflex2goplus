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
        if (Schema::hasColumn('planning_shifts', 'subsidiary_name')) {
            Schema::table('planning_shifts', function (Blueprint $table) {
                $table->dropColumn('subsidiary_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('planning_shifts', 'subsidiary_name')) {
            Schema::table('planning_shifts', function (Blueprint $table) {
                $table->string('subsidiary_name')->nullable()->after('company_name');
            });
        }
    }
};
