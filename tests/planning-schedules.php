<?php

use App\Models\PlanningShift;
use App\Services\PlanningCsvImportService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$check = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
    }
    echo 'PASS: '.$message.PHP_EOL;
};

$storedPath = 'csv/schedule-regression-'.bin2hex(random_bytes(8)).'.csv';
$writeRows = static function (array $rows) use ($storedPath): void {
    $stream = fopen('php://temp', 'w+');
    fputcsv($stream, array_keys($rows[0]), ',', '"', '');
    foreach ($rows as $row) {
        fputcsv($stream, array_values($row), ',', '"', '');
    }
    rewind($stream);
    Storage::disk('local')->put($storedPath, stream_get_contents($stream));
    fclose($stream);
};

$base = [
    'Relatie - Naam' => 'Schedule regression company',
    'Datum' => '02-11-2099',
    'Begintijd' => '07:00',
    'Eindtijd' => '15:45',
    'Ingeplande entiteiten - Flexwerker - Registratienummer ' => 'schedule-test',
    'Ingeplande entiteiten - Flexwerker - Naam' => 'Schedule test worker',
    'Ingeplande entiteiten - Starttijd' => '',
    'Ingeplande entiteiten - Eindtijd' => '',
    'Ingeplande entiteiten - Afwezigheid' => 'Vakantie',
    'Ingeplande entiteiten - Flexwerker - Status' => 'Actief',
    'Ingeplande entiteiten - Status' => '',
];
$different = array_replace($base, [
    'Ingeplande entiteiten - Starttijd' => '08:30',
    'Ingeplande entiteiten - Eindtijd' => '17:15',
    'Ingeplande entiteiten - Afwezigheid' => 'Vrij',
]);
$overnight = array_replace($base, [
    'Begintijd' => '23:00',
    'Eindtijd' => '07:00',
    'Ingeplande entiteiten - Starttijd' => '22:30',
    'Ingeplande entiteiten - Eindtijd' => '06:30',
    'Ingeplande entiteiten - Afwezigheid' => 'Ziek',
    'Ingeplande entiteiten - Flexwerker - Status' => ' Stop ',
    'Ingeplande entiteiten - Status' => 'STOP.',
]);
$workerOnly = array_replace($different, [
    'Begintijd' => '',
    'Eindtijd' => '',
    'Ingeplande entiteiten - Afwezigheid' => 'Reserve',
    'Ingeplande entiteiten - Status' => 'No show.',
]);
$writeRows([$base, $different, $different, $overnight, $workerOnly]);
$date = Carbon\Carbon::createFromFormat('d-m-Y', $base['Datum']);

DB::beginTransaction();
try {
    $service = $app->make(PlanningCsvImportService::class);
    $import = $service->importFromStoredPath($storedPath, (int) $date->format('W'), (int) $date->format('o'));
    $check($import->row_count === 5 && $import->assignment_count === 4, 'Distinct worker schedules survive; exact duplicates are removed');
    $shifts = PlanningShift::where('planning_import_id', $import->id)->with('assignments')->get();
    $parent = $shifts->first(fn ($shift) => substr($shift->start_time, 0, 5) === '07:00');
    $check($parent !== null && $parent->assignments->count() === 2, 'Different worker times remain attached to the parent shift');
    $check($parent->assignments->contains(fn ($assignment) => $assignment->start_time === '08:30:00' && $assignment->end_time === '17:15:00'), 'Individual start and end times are stored');
    $check($parent->assignments->contains(fn ($assignment) => $assignment->start_time === '07:00:00' && $assignment->end_time === '15:45:00'), 'Blank individual times fall back to parent times');
    $check($parent->assignments->pluck('absence_status')->sort()->values()->all() === ['Vakantie', 'Vrij'], 'Absence status is stored separately for each worker assignment');
    $night = $shifts->first(fn ($shift) => substr($shift->start_time, 0, 5) === '23:00');
    $check($night !== null && $night->assignments->first()->end_time === '06:30:00' && $night->shift_end_at->greaterThan($night->shift_start_at), 'Overnight schedules are retained');
    $check($shifts->contains(fn ($shift) => substr($shift->start_time, 0, 5) === '08:30' && $shift->assignments->count() === 1), 'Workers with only individual schedule times are retained');
    $again = $service->importFromStoredPath($storedPath, (int) $date->format('W'), (int) $date->format('o'));
    $check($again->id === $import->id && $again->assignment_count === 4, 'Reimport preserves the week and assignment count');
    $request = Illuminate\Http\Request::create('/week-data', 'GET', [
        'week' => (int) $date->format('W'),
        'year' => (int) $date->format('o'),
    ]);
    $request->attributes->set('access_scope', 'company');
    $request->attributes->set('access_company_name', $base['Relatie - Naam']);
    $app->instance('request', $request);
    $controller = $app->make(App\Http\Controllers\Spa\WeekCalendarController::class);
    $html = $controller->weekData($request)->getData(true)['html'];
    $check(substr_count($html, '<strong>Schedule test worker</strong>') === 4, 'Every distinct worker assignment is rendered in the calendar');
    $check(str_contains($html, '08:30 - 17:15') && str_contains($html, '22:30 - 06:30'), 'Calendar displays differing daytime and overnight schedules');
    foreach (['Vakantie', 'Vrij', 'Reserve', 'Stop', 'Ziek', 'No show.'] as $status) {
        $check(str_contains($html, $status), 'Calendar displays status '.$status);
    }
    $check(substr_count($html, 'class="worker-status ') === 6 && ! str_contains($html, 'STOP.'), 'Each distinct status gets its own label with case-insensitive deduplication');
    foreach (['Stop' => 'red', 'Vrij' => 'yellow', 'Vakantie' => 'yellow', 'Reserve' => 'blue', 'Ziek' => 'blue', 'No show.' => 'purple'] as $status => $color) {
        $check(str_contains($html, 'class="worker-status worker-status-'.$color.'">'.$status.'</div>'), 'Status '.$status.' uses the '.$color.' label');
    }
    $check(! str_contains($html, 'Actief'), 'Routine active status does not clutter worker cards');

    $event = array_replace($base, [
        'Ingeplande entiteiten - Afwezigheid' => '',
        'Ingeplande entiteiten - Status' => 'Bijzonder verlof',
    ]);
    $vacation = array_replace($event, ['Ingeplande entiteiten - Status' => 'Vakantie']);
    $workerEvent = array_replace($event, [
        'Ingeplande entiteiten - Flexwerker - Status' => 'Opleiding',
        'Ingeplande entiteiten - Status' => '',
    ]);
    $writeRows([$event, $vacation, $workerEvent, $event]);
    $eventImport = $service->importFromStoredPath($storedPath, (int) $date->format('W'), (int) $date->format('o'));
    $check($eventImport->assignment_count === 3, 'Distinct event statuses for the same worker and schedule survive while exact duplicates are removed');
    $eventHtml = $controller->weekData($request)->getData(true)['html'];
    foreach (['Bijzonder verlof', 'Vakantie', 'Opleiding'] as $status) {
        $check(str_contains($eventHtml, '">'.$status.'</div>'), 'Calendar retains event label '.$status.' without an absence value');
    }
    $check(! str_contains($eventHtml, 'Actief'), 'Custom events do not restore routine active labels');

    $incomplete = $event;
    unset($incomplete['Ingeplande entiteiten - Status']);
    $writeRows([$incomplete]);
    $rejected = false;
    try {
        $service->importFromStoredPath($storedPath, (int) $date->format('W'), (int) $date->format('o'));
    } catch (RuntimeException $exception) {
        $rejected = str_contains($exception->getMessage(), 'missing "Ingeplande entiteiten - Status"');
    }
    $check($rejected, 'Expanded exports without the planning-status column fail explicitly');
    $check($eventImport->fresh()->assignment_count === 3 && $controller->weekData($request)->getData(true)['html'] === $eventHtml, 'Rejected exports leave the existing week and its events unchanged');

    $writeRows([array_replace($event, ['Ingeplande entiteiten - Status' => ''])]);
    $emptyStatusImport = $service->importFromStoredPath($storedPath, (int) $date->format('W'), (int) $date->format('o'));
    $check($emptyStatusImport->assignment_count === 1, 'Expanded exports with a present but blank planning-status column remain valid');

    $legacy = $base;
    unset($legacy['Ingeplande entiteiten - Starttijd'], $legacy['Ingeplande entiteiten - Eindtijd'], $legacy['Ingeplande entiteiten - Afwezigheid'], $legacy['Ingeplande entiteiten - Status']);
    $writeRows([$legacy]);
    $legacyImport = $service->importFromStoredPath($storedPath, (int) $date->format('W'), (int) $date->format('o'));
    $check($legacyImport->assignment_count === 1, 'Older exports without individual time columns still import');
    $legacyAssignment = PlanningShift::where('planning_import_id', $legacyImport->id)->first()->assignments->first();
    $check($legacyAssignment->start_time === '07:00:00' && $legacyAssignment->end_time === '15:45:00', 'Older exports use the shift schedule');
    $check($legacyAssignment->absence_status === null, 'Older exports without absence status remain compatible');
    $legacyHtml = $controller->weekData($request)->getData(true)['html'];
    $check(! str_contains($legacyHtml, 'class="worker-status '), 'Worker cards without an exceptional status have no empty label');
} finally {
    DB::rollBack();
    Storage::disk('local')->delete($storedPath);
}