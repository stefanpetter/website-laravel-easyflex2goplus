<?php

use App\Services\PlanningCsvImportService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('Keep it simple.');
})->purpose('Display an inspiration message');

Schedule::call(function (PlanningCsvImportService $planningCsvImportService): void {
    $planningCsvImportService->importPendingCsvFiles();
})
    ->name('planning:import-pending-csv')
    ->everyThirtyMinutes();
