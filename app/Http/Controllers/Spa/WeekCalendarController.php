<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\CompanyAccessToken;
use App\Models\PlanningImport;
use App\Models\PlanningShift;
use App\Services\PlanningCsvImportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WeekCalendarController extends Controller
{
	public function __construct(private readonly PlanningCsvImportService $planningCsvImportService)
	{
	}

    public function index(Request $request)
    {
        $now = Carbon::now();
        $currentWeek = (int) $now->format('W');
        $currentYear = (int) $now->format('o');

        $week = (int) request('week', $currentWeek);
        $year = (int) request('year', $currentYear);
        $search = trim((string) request('q', ''));

        // Only auto-import from the latest CSV for the current week.
        if ($week === $currentWeek && $year === $currentYear) {
            $import = $this->planningCsvImportService->ensureLatestCsvImportedForWeek($week, $year);
        } else {
            $import = PlanningImport::query()
                ->where('iso_week', $week)
                ->where('iso_year', $year)
                ->latest('imported_at')
                ->first();
        }

        $accessScope = (string) $request->attributes->get('access_scope');
        $restrictCompanyName = $request->attributes->get('access_company_name');

        $data = $this->buildViewData($week, $year, $search, $import, $currentWeek, $currentYear, $restrictCompanyName);
        $data = array_merge($data, $this->buildTokenSharingData($accessScope, $data['companies']));

        return view('spa.week-calendar', array_merge($data, [
            'token' => request('token'),
            'accessScope' => $accessScope,
        ]));
    }

    public function weekData(Request $request): JsonResponse
    {
        $now = Carbon::now();
        $currentWeek = (int) $now->format('W');
        $currentYear = (int) $now->format('o');

        $week = (int) request('week', $currentWeek);
        $year = (int) request('year', $currentYear);
        $search = trim((string) request('q', ''));

        $import = PlanningImport::query()
            ->where('iso_week', $week)
            ->where('iso_year', $year)
            ->latest('imported_at')
            ->first();

        $accessScope = (string) $request->attributes->get('access_scope');
        $restrictCompanyName = $request->attributes->get('access_company_name');

        $data = $this->buildViewData($week, $year, $search, $import, $currentWeek, $currentYear, $restrictCompanyName);
        $data = array_merge($data, $this->buildTokenSharingData($accessScope, $data['companies']));
        $data['accessScope'] = $accessScope;

        $html = view('spa.partials.week-body', $data)->render();

        return response()->json([
            'html' => $html,
            'week' => $week,
            'year' => $year,
            'isCurrentWeek' => $data['isCurrentWeek'],
            'hasNextWeekData' => $data['hasNextWeekData'],
            'import' => $import ? [
                'imported_at' => $import->imported_at?->format('d-m-Y H:i'),
                'shift_count' => $data['resultShiftCount'],
                'company_count' => $data['resultCompanyCount'],
            ] : null,
        ]);
    }

    public function renewCompanyToken(Request $request, CompanyAccessToken $companyAccessToken): JsonResponse
    {
        if ($request->attributes->get('access_scope') !== 'admin') {
            abort(403);
        }

        $companyAccessToken->update(['token' => CompanyAccessToken::generateToken()]);

        return response()->json([
            'company_name' => $companyAccessToken->company_name,
            'token' => $companyAccessToken->token,
            'url' => route('calendar.week', ['token' => $companyAccessToken->token]),
        ]);
    }

    /**
     * Provision (or fetch) a share URL/token per visible company for admin and view-all tokens.
     */
    private function buildTokenSharingData(string $accessScope, array $companies): array
    {
        if (! in_array($accessScope, ['admin', 'view_all'], true)) {
            return ['companyTokens' => []];
        }

        $companyTokens = [];

        foreach ($companies as $company) {
            $companyToken = CompanyAccessToken::findOrCreateForCompany($company['company_name']);

            $companyTokens[$company['company_name']] = [
                'id' => $companyToken->id,
                'url' => route('calendar.week', ['token' => $companyToken->token]),
            ];
        }

        return ['companyTokens' => $companyTokens];
    }

    private function nextWeekHasData(int $week, int $year): bool
    {
        $nextWeekStart = Carbon::now()->setISODate($year, $week)->startOfWeek()->addWeek();

        return PlanningImport::query()
            ->where('iso_week', (int) $nextWeekStart->format('W'))
            ->where('iso_year', (int) $nextWeekStart->format('o'))
            ->exists();
    }

    private function buildViewData(int $week, int $year, string $search, ?PlanningImport $import, int $currentWeek, int $currentYear, ?string $restrictCompanyName = null): array
    {
        $weekStart = Carbon::now()->setISODate($year, $week)->startOfWeek();

        $dayTemplate = [];
        $daysByDate = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
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

                $companyName = $shift->company_name ?: 'Unknown company';

                if ($restrictCompanyName !== null && $companyName !== $restrictCompanyName) {
                    continue;
                }

                $assignments = $shift->assignments->map(function ($assignment) use ($search, $shift) {
                    $workerName = (string) ($assignment->worker_name ?? 'Unknown worker');
                    $registration = (string) ($assignment->worker_registration_number ?? '');
                    $startTime = substr((string) ($assignment->start_time ?? $shift->start_time), 0, 5);
                    $endTime = substr((string) ($assignment->end_time ?? $shift->end_time), 0, 5);
                    $statusLabels = collect([
                        'absence' => $assignment->absence_status,
                        'worker' => $assignment->worker_status,
                        'planning' => $assignment->planning_status,
                    ])->filter(fn (?string $status, string $source): bool => filled($status) && (
                        $source === 'absence' || in_array(Str::lower(rtrim(trim($status), '.')), [
                            'vakantie', 'vrij', 'reserve', 'stop', 'ziek', 'no show',
                        ], true)
                    ))->map(fn (string $status): string => trim($status))
                        ->unique(fn (string $status): string => Str::lower(rtrim($status, '.')))
                        ->values()
                        ->all();
                    $matchesSearch = $search === ''
                        ? false
                        : Str::contains(Str::lower($workerName), Str::lower($search))
                            || Str::contains(Str::lower($registration), Str::lower($search));

                    return [
                        'worker_name' => $workerName,
                        'registration' => $registration,
                        'worker_status' => $assignment->worker_status,
                        'planning_status' => $assignment->planning_status,
                        'status_labels' => $statusLabels,
                        'is_driver' => $assignment->is_driver,
                        'matches_search' => $matchesSearch,
                        'time_label' => sprintf('%s - %s', $startTime, $endTime),
                        'has_different_schedule' => $startTime !== substr((string) $shift->start_time, 0, 5)
                            || $endTime !== substr((string) $shift->end_time, 0, 5),
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

                $driverNames = collect($assignments)
                    ->filter(fn (array $assignment): bool => $assignment['is_driver'])
                    ->pluck('worker_name')
                    ->values()
                    ->all();

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
                    'role_name' => $shift->role_name,
                    'function_name' => $shift->function_name,
                    'cost_center_name' => $shift->cost_center_name,
                    'work_address' => $shift->work_address,
                    'time_label' => sprintf('%s - %s', $shift->start_time, $shift->end_time),
                    'shift_type_1' => $shift->shift_type_1,
                    'shift_type_2' => $shift->shift_type_2,
                    'assignments' => $assignments,
                    'driver_names' => $driverNames,
                ];

                $companies[$companyName]['shift_count']++;
                $resultShiftCount++;
            }
        }

        ksort($companies, SORT_NATURAL | SORT_FLAG_CASE);
        usort($workerOptions, fn (array $a, array $b): int => strnatcasecmp($a['label'], $b['label']));

        return [
            'week' => $week,
            'year' => $year,
            'isCurrentWeek' => $week === $currentWeek && $year === $currentYear,
            'hasNextWeekData' => $this->nextWeekHasData($week, $year),
            'companies' => array_values($companies),
            'search' => $search,
            'workerOptions' => array_values($workerOptions),
            'resultShiftCount' => $resultShiftCount,
            'resultCompanyCount' => count($companies),
            'import' => $import,
        ];
    }
}
