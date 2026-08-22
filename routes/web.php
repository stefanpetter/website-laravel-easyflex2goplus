<?php

use App\Http\Controllers\Spa\WeekCalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeekCalendarController::class, 'index'])->name('calendar.week');
Route::get('/week-data', [WeekCalendarController::class, 'weekData'])->name('calendar.week.data');
Route::post('/companies/{companyAccessToken}/renew-token', [WeekCalendarController::class, 'renewCompanyToken'])->name('calendar.company.renew-token');
