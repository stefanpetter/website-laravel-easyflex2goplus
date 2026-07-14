<?php

use App\Http\Controllers\Spa\WeekCalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeekCalendarController::class, 'index'])->name('calendar.week');
