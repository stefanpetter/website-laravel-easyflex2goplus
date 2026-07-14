<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\CsvController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/flexworkers/import', [ImportController::class, 'index'])->middleware(['auth:sanctum']);

Route::get('/reports/generatedailyreport', [CsvController::class, 'createDailyCSV'])->middleware(['auth:sanctum']);
Route::get('/reports/senddailyreport', [CsvController::class, 'sendDailyCSV'])->middleware(['auth:sanctum']);

Route::get('/reports/generateweeklyreport', [CsvController::class, 'createWeeklyCSV'])->middleware(['auth:sanctum']);

Route::get('/reports/generatedailysnelstartreport', [CsvController::class, 'createDailySnelstartCSV'])->middleware(['auth:sanctum']);
