<?php

namespace App\Http\Controllers;

use App\Services\PlanningCsvImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CsvUploadController extends Controller
{
	public function __construct(private readonly PlanningCsvImportService $planningCsvImportService)
	{
	}

	public function store(Request $request): JsonResponse
	{
		$validated = $request->validate([
			'file' => ['required', 'file', 'mimes:csv,txt'],
			'week' => ['nullable', 'integer', 'between:1,53'],
			'year' => ['nullable', 'integer', 'between:2000,2099'],
		]);

		$fileName = now()->format('YmdHisv') . '.csv';
		$storedPath = $validated['file']->storeAs('csv', $fileName);
		$week = (int) ($validated['week'] ?? now()->format('W'));
		$year = (int) ($validated['year'] ?? now()->format('o'));

		try {
			$import = $this->planningCsvImportService->importFromStoredPath($storedPath, $week, $year);
			$this->planningCsvImportService->markAsImported($storedPath);
		} catch (Throwable $exception) {
			return response()->json([
				'ok' => false,
				'message' => 'CSV uploaded, but import failed.',
				'path' => $storedPath,
				'error' => $exception->getMessage(),
			], 422);
		}

		return response()->json([
			'ok' => true,
			'path' => $storedPath,
			'import_id' => $import->id,
			'week' => $import->iso_week,
			'year' => $import->iso_year,
			'rows' => $import->row_count,
			'shifts' => $import->shift_count,
			'assignments' => $import->assignment_count,
		], 201);
	}
}