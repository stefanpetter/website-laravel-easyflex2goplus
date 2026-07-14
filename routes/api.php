<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
	Route::get('/status', function (Request $request) {
		return response()->json([
			'ok' => true,
			'app' => config('app.name'),
			'time' => now()->toIso8601String(),
		]);
	})->name('api.v1.status');
});
