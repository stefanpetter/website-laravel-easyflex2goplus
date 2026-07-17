<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\PlanningShift;
use App\Services\PlanningCsvImportService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WeekCalendarController extends Controller
{
	public function __construct(private readonly PlanningCsvImportService $planningCsvImportService)
	{
	}

    public function index()
    {
        $currentDate = Carbon::now();
        $week = (int) $currentDate->format('W');
        $year = (int) $currentDate->format('o');
        $search = trim((string) request('q', ''));

        $currentWeekStart = $currentDate->copy()->setISODate($year, $week)->startOfWeek();

        $import = $this->planningCsvImportService->ensureLatestCsvImportedForWeek($week, $year);

        $dayTemplate = [];
        $daysByDate = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $currentWeekStart->copy()->addDays($i);
            $dayData = [
                'date' => $day->toDateString(),
                'title' => $day->format('l'),
                'subtitle' => $day->format('d-m-Y'),
                'shifts' => [],
            ];

            $dayTemplate[] = $dayData;
            $daysByDate[$dayData['date']] = count($dayTemplate) - 1;
        }

        $resultShiftCount = 0;
        $workerOptions = [];
        $companies = [];

        if ($import !== null) {
            $shifts = PlanningShift::query()
                ->where('planning_import_id', $import->id)
                ->whereHas('assignments')
                ->with(['assignments' => function ($query) {
                    $query->orderByDesc('is_driver')->orderBy('worker_name');
                }])
                ->orderBy('shift_start_at')
                ->get();

            foreach ($shifts as $shift) {
                $dayDate = $shift->shift_date?->toDateString();

                if ($dayDate === null || ! isset($daysByDate[$dayDate])) {
                    continue;
                }

                $assignments = $shift->assignments->map(function ($assignment) use ($search) {
                    $workerName = (string) ($assignment->worker_name ?? 'Unknown worker');
                    $registration = (string) ($assignment->worker_registration_number ?? '');
                    $matchesSearch = $search === ''
                        ? false
                        : Str::contains(Str::lower($workerName), Str::lower($search))
                            || Str::contains(Str::lower($registration), Str::lower($search));

                    return [
                        'worker_name' => $workerName,
                        'registration' => $registration,
                        'worker_status' => $assignment->worker_status,
                        'planning_status' => $assignment->planning_status,
                        'is_driver' => $assignment->is_driver,
                        'matches_search' => $matchesSearch,
                    ];
                })->values()->all();

                foreach ($assignments as $assignmentOption) {
                    $workerKey = mb_strtolower(trim($assignmentOption['worker_name'].'|'.$assignmentOption['registration']));

                    if (! isset($workerOptions[$workerKey])) {
                        $workerOptions[$workerKey] = [
                            'value' => $assignmentOption['registration'] !== ''
                                ? $assignmentOption['registration']
                                : $assignmentOption['worker_name'],
                            'label' => $assignmentOption['registration'] !== ''
                                ? sprintf('%s (#%s)', $assignmentOption['worker_name'], $assignmentOption['registration'])
                                : $assignmentOption['worker_name'],
                        ];
                    }
                }

                $hasMatch = collect($assignments)->contains(fn (array $assignment): bool => $assignment['matches_search']);

                if ($search !== '' && ! $hasMatch) {
                    continue;
                }

                $driver = collect($assignments)->first(fn (array $assignment): bool => $assignment['is_driver']);

                $companyName = $shift->company_name ?: 'Unknown company';
                if (! isset($companies[$companyName])) {
                    $companies[$companyName] = [
                        'company_name' => $companyName,
                        'shift_count' => 0,
                        'days' => array_map(static fn (array $day): array => [
                            'date' => $day['date'],
                            'title' => $day['title'],
                            'subtitle' => $day['subtitle'],
                            'shifts' => [],
                        ], $dayTemplate),
                    ];
                }

                $companies[$companyName]['days'][$daysByDate[$dayDate]]['shifts'][] = [
                    'company_name' => $companyName,
                    'subsidiary_name' => $shift->subsidiary_name,
                    'role_name' => $shift->role_name,
                    'cost_center_name' => $shift->cost_center_name,
                    'work_address' => $shift->work_address,
                    'time_label' => sprintf('%s - %s', $shift->start_time, $shift->end_time),
                    'shift_type_1' => $shift->shift_type_1,
                    'shift_type_2' => $shift->shift_type_2,
                    'assignments' => $assignments,
                    'driver_name' => $driver['worker_name'] ?? null,
                ];

                $companies[$companyName]['shift_count']++;
                $resultShiftCount++;
            }
        }

        ksort($companies, SORT_NATURAL | SORT_FLAG_CASE);
        usort($workerOptions, fn (array $a, array $b): int => strnatcasecmp($a['label'], $b['label']));

        return view('spa.week-calendar', [
            'week' => $week,
            'year' => $year,
            'companies' => array_values($companies),
            'token' => request('token'),
            'search' => $search,
            'workerOptions' => array_values($workerOptions),
            'resultShiftCount' => $resultShiftCount,
            'resultCompanyCount' => count($companies),
            'import' => $import,
        ]);
    }
}
