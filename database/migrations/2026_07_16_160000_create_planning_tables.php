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
        Schema::create('planning_imports', function (Blueprint $table) {
            $table->id();
            $table->string('source_file');
            $table->unsignedSmallInteger('iso_week');
            $table->unsignedSmallInteger('iso_year');
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedInteger('shift_count')->default(0);
            $table->unsignedInteger('assignment_count')->default(0);
            $table->timestamp('imported_at');
            $table->timestamps();

            $table->unique(['source_file', 'iso_week', 'iso_year'], 'planning_import_source_week_year_unique');
            $table->index(['iso_year', 'iso_week']);
        });

        Schema::create('planning_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_import_id')->constrained('planning_imports')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('subsidiary_name')->nullable();
            $table->string('role_name')->nullable();
            $table->string('cost_center_name')->nullable();
            $table->string('work_address')->nullable();
            $table->date('shift_date');
            $table->string('day_name', 24)->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->dateTime('shift_start_at');
            $table->dateTime('shift_end_at');
            $table->string('shift_type_1')->nullable();
            $table->string('shift_type_2')->nullable();
            $table->timestamps();

            $table->index(['planning_import_id', 'shift_date']);
            $table->index(['planning_import_id', 'shift_start_at']);
            $table->index('company_name');
        });

        Schema::create('planning_shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_shift_id')->constrained('planning_shifts')->cascadeOnDelete();
            $table->string('worker_registration_number')->nullable();
            $table->string('worker_name')->nullable();
            $table->string('worker_status')->nullable();
            $table->string('planning_status')->nullable();
            $table->boolean('is_driver')->default(false);
            $table->timestamps();

            $table->index('planning_shift_id');
            $table->index('worker_registration_number');
            $table->index('worker_name');
            $table->index('is_driver');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_shift_assignments');
        Schema::dropIfExists('planning_shifts');
        Schema::dropIfExists('planning_imports');
    }
};
