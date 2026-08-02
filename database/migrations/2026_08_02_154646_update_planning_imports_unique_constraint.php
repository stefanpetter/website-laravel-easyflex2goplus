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
        Schema::table('planning_imports', function (Blueprint $table) {
            $table->dropUnique('planning_import_source_week_year_unique');
            $table->unique(['iso_week', 'iso_year'], 'planning_imports_week_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_imports', function (Blueprint $table) {
            $table->dropUnique('planning_imports_week_year_unique');
            $table->unique(['source_file', 'iso_week', 'iso_year'], 'planning_import_source_week_year_unique');
        });
    }
};
