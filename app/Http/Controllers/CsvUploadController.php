<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CsvUploadController extends Controller
{
	public function store(Request $request): JsonResponse
	{
		$validated = $request->validate([
			'file' => ['required', 'file', 'mimes:csv,txt'],
		]);

		$fileName = now()->format('YmdHisv') . '.csv';
		$storedPath = $validated['file']->storeAs('csv', $fileName);

		return response()->json([
			'ok' => true,
			'path' => $storedPath,
		], 201);
	}
}