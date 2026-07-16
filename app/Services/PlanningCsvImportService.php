<?php

namespace App\Services;

use App\Models\PlanningImport;
use App\Models\PlanningShift;
use App\Models\PlanningShiftAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SplFileObject;
use Throwable;

class PlanningCsvImportService
{
    private const IMPORTED_PREFIX = 'imported_';
    private const DATE_FORMAT = 'd-m-Y';

    private const COLUMN_COMPANY = 'Relatie - Naam';
    private const COLUMN_SUBSIDIARY = 'Relatie - Werkmaatschappij - Naam';
    private const COLUMN_ROLE = 'Hoofdfunctie - Naam';
    private const COLUMN_COST_CENTER = 'Kostenplaats - Naam';
    private const COLUMN_WORK_ADDRESS = 'Meld- / werkadres - Omschrijving';
    private const COLUMN_DATE = 'Datum';
    private const COLUMN_DAY = 'Dag';
    private const COLUMN_START_TIME = 'Begintijd';
    private const COLUMN_END_TIME = 'Eindtijd';
    private const COLUMN_TYPE_1 = 'Soort - Naam 1';
    private const COLUMN_TYPE_2 = 'Soort - Naam 2';
    private const COLUMN_WORKER_REG = 'Ingeplande entiteiten - Flexwerker - Registratienummer ';
    private const COLUMN_WORKER_NAME = 'Ingeplande entiteiten - Flexwerker - Naam';
    private const COLUMN_WORKER_STATUS = 'Ingeplande entiteiten - Flexwerker - Status';
    private const COLUMN_PLANNING_STATUS = 'Ingeplande entiteiten - Status';
    private const COLUMN_DRIVER = 'Ingeplande entiteiten - Chauffeur';

    public function importFromStoredPath(string $storedPath, int $isoWeek, int $isoYear): PlanningImport
    {
        $absolutePath = Storage::disk('local')->path($storedPath);

        if (! is_file($absolutePath)) {
            throw new RuntimeException('CSV file does not exist on local storage disk.');
        }

        $rows = $this->readCsvRows($absolutePath);

        return $this->importRowsForWeek($rows, $storedPath, $isoWeek, $isoYear);
    }

    /**
     * @return array<int, PlanningImport>
     */
    public function importPendingCsvFiles(): array
    {
        $imports = [];

        foreach ($this->findPendingStoredCsvPaths() as $storedPath) {
            $importsForFile = $this->importByContainedWeeks($storedPath);
            $this->markStoredFileAsImported($storedPath);
            $imports = [...$imports, ...$importsForFile];
        }

        return $imports;
    }

    public function markAsImported(string $storedPath): void
    {
        $this->markStoredFileAsImported($storedPath);
    }

    public function ensureLatestCsvImportedForWeek(int $isoWeek, int $isoYear): ?PlanningImport
    {
        $existingImport = PlanningImport::query()
            ->where('iso_week', $isoWeek)
            ->where('iso_year', $isoYear)
            ->latest('imported_at')
            ->first();

        if ($existingImport !== null && $this->isImportComplete($existingImport)) {
            return $existingImport;
        }

        $latestStoredPath = $this->findLatestStoredCsvPath();

        if ($latestStoredPath === null) {
            return $existingImport;
        }

        try {
            return $this->importFromStoredPath($latestStoredPath, $isoWeek, $isoYear);
        } catch (Throwable) {
            return $existingImport;
        }
    }

    /**
     * @return array<int, PlanningImport>
     */
    private function importByContainedWeeks(string $storedPath): array
    {
        $absolutePath = Storage::disk('local')->path($storedPath);

        if (! is_file($absolutePath)) {
            throw new RuntimeException('CSV file does not exist on local storage disk.');
        }

        $rows = $this->readCsvRows($absolutePath);
        $weeks = $this->extractWeeksFromRows($rows);

        if ($weeks === []) {
            throw new RuntimeException('CSV file contains no assignable week data.');
        }

        $imports = [];

        foreach ($weeks as $weekData) {
            $imports[] = $this->importRowsForWeek(
                $rows,
                $storedPath,
                $weekData['week'],
                $weekData['year']
            );
        }

        return $imports;
    }

    private function importRowsForWeek(array $rows, string $sourceFile, int $isoWeek, int $isoYear): PlanningImport
    {
        return DB::transaction(function () use ($rows, $sourceFile, $isoWeek, $isoYear) {
            $import = PlanningImport::query()->firstOrNew([
                'source_file' => $sourceFile,
                'iso_week' => $isoWeek,
                'iso_year' => $isoYear,
            ]);

            if ($import->exists) {
                PlanningShift::query()->where('planning_import_id', $import->id)->delete();
            }

            $import->fill([
                'row_count' => 0,
                'shift_count' => 0,
                'assignment_count' => 0,
                'imported_at' => now(),
            ]);
            $import->save();

            $shiftIdsByKey = [];
            $assignmentDedup = [];
            $rowCount = 0;
            $assignmentCount = 0;

            foreach ($rows as $row) {
                $dateValue = $this->clean($row[self::COLUMN_DATE] ?? null);
                $startTime = $this->clean($row[self::COLUMN_START_TIME] ?? null);
                $endTime = $this->clean($row[self::COLUMN_END_TIME] ?? null);
                $companyName = $this->clean($row[self::COLUMN_COMPANY] ?? null);
                $workerName = $this->clean($row[self::COLUMN_WORKER_NAME] ?? null);
                $registrationNumber = $this->clean($row[self::COLUMN_WORKER_REG] ?? null);

                if ($dateValue === null || $startTime === null || $endTime === null || $companyName === null) {
                    continue;
                }

                if ($workerName === null && $registrationNumber === null) {
                    continue;
                }

                $shiftDate = $this->parseDate($dateValue);

                if ($shiftDate === null) {
                    continue;
                }

                if ((int) $shiftDate->format('o') !== $isoYear || (int) $shiftDate->format('W') !== $isoWeek) {
                    continue;
                }

                $shiftStartAt = $this->parseDateTime($shiftDate, $startTime);
                $shiftEndAt = $this->parseDateTime($shiftDate, $endTime);

                if ($shiftStartAt === null || $shiftEndAt === null) {
                    continue;
                }

                $rowCount++;

                if ($shiftEndAt->lessThanOrEqualTo($shiftStartAt)) {
                    $shiftEndAt->addDay();
                }

                $shiftData = [
                    'planning_import_id' => $import->id,
                    'company_name' => $companyName,
                    'subsidiary_name' => $this->clean($row[self::COLUMN_SUBSIDIARY] ?? null),
                    'role_name' => $this->clean($row[self::COLUMN_ROLE] ?? null),
                    'cost_center_name' => $this->clean($row[self::COLUMN_COST_CENTER] ?? null),
                    'work_address' => $this->clean($row[self::COLUMN_WORK_ADDRESS] ?? null),
                    'shift_date' => $shiftDate->toDateString(),
                    'day_name' => $this->clean($row[self::COLUMN_DAY] ?? null),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'shift_start_at' => $shiftStartAt,
                    'shift_end_at' => $shiftEndAt,
                    'shift_type_1' => $this->clean($row[self::COLUMN_TYPE_1] ?? null),
                    'shift_type_2' => $this->clean($row[self::COLUMN_TYPE_2] ?? null),
                ];

                $shiftKey = $this->buildShiftKey($shiftData);

                if (! isset($shiftIdsByKey[$shiftKey])) {
                    $shift = PlanningShift::query()->create($shiftData);
                    $shiftIdsByKey[$shiftKey] = $shift->id;
                }

                $assignmentKey = sha1(implode('|', [
                    $shiftIdsByKey[$shiftKey],
                    mb_strtolower((string) $registrationNumber),
                    mb_strtolower((string) $workerName),
                ]));

                if (isset($assignmentDedup[$assignmentKey])) {
                    continue;
                }

                PlanningShiftAssignment::query()->create([
                    'planning_shift_id' => $shiftIdsByKey[$shiftKey],
                    'worker_registration_number' => $registrationNumber,
                    'worker_name' => $workerName,
                    'worker_status' => $this->clean($row[self::COLUMN_WORKER_STATUS] ?? null),
                    'planning_status' => $this->clean($row[self::COLUMN_PLANNING_STATUS] ?? null),
                    'is_driver' => $this->toDriverFlag($row[self::COLUMN_DRIVER] ?? null),
                ]);

                $assignmentDedup[$assignmentKey] = true;
                $assignmentCount++;
            }

            if ($assignmentCount === 0) {
                throw new RuntimeException(sprintf(
                    'No assignable rows found for week %d and year %d in CSV.',
                    $isoWeek,
                    $isoYear
                ));
            }

            $import->fill([
                'row_count' => $rowCount,
                'shift_count' => count($shiftIdsByKey),
                'assignment_count' => $assignmentCount,
            ]);
            $import->save();

            // Keep only the latest successful import for this week/year.
            PlanningImport::query()
                ->where('iso_week', $isoWeek)
                ->where('iso_year', $isoYear)
                ->where('id', '!=', $import->id)
                ->delete();

            return $import;
        });
    }

    /**
     * @return array<int, array{week:int,year:int}>
     */
    private function extractWeeksFromRows(array $rows): array
    {
        $weeks = [];

        foreach ($rows as $row) {
            $dateValue = $this->clean($row[self::COLUMN_DATE] ?? null);
            $workerName = $this->clean($row[self::COLUMN_WORKER_NAME] ?? null);
            $registrationNumber = $this->clean($row[self::COLUMN_WORKER_REG] ?? null);

            if ($dateValue === null || ($workerName === null && $registrationNumber === null)) {
                continue;
            }

            $date = $this->parseDate($dateValue);

            if ($date === null) {
                continue;
            }

            $year = (int) $date->format('o');
            $week = (int) $date->format('W');
            $key = $year.'-'.$week;

            if (! isset($weeks[$key])) {
                $weeks[$key] = ['week' => $week, 'year' => $year];
            }
        }

        ksort($weeks);

        return array_values($weeks);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function readCsvRows(string $absolutePath): array
    {
        $file = new SplFileObject($absolutePath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl(',', '"', '\\');

        $header = null;
        $rows = [];

        foreach ($file as $csvRow) {
            if (! is_array($csvRow)) {
                continue;
            }

            if ($header === null) {
                $header = array_map(function ($value) {
                    $value = is_string($value) ? trim($value) : '';

                    return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
                }, $csvRow);

                continue;
            }

            if (count($csvRow) === 1 && ($csvRow[0] === null || trim((string) $csvRow[0]) === '')) {
                continue;
            }

            $row = [];
            foreach ($header as $index => $column) {
                if ($column === '') {
                    continue;
                }

                $row[$column] = array_key_exists($index, $csvRow) ? $this->clean($csvRow[$index]) : null;
            }

            if ($row !== []) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function clean(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function buildShiftKey(array $shiftData): string
    {
        return sha1(implode('|', [
            $shiftData['planning_import_id'],
            mb_strtolower((string) $shiftData['company_name']),
            mb_strtolower((string) $shiftData['subsidiary_name']),
            mb_strtolower((string) $shiftData['role_name']),
            mb_strtolower((string) $shiftData['cost_center_name']),
            mb_strtolower((string) $shiftData['work_address']),
            $shiftData['shift_date'],
            $shiftData['start_time'],
            $shiftData['end_time'],
            mb_strtolower((string) $shiftData['shift_type_1']),
            mb_strtolower((string) $shiftData['shift_type_2']),
        ]));
    }

    private function toDriverFlag(mixed $value): bool
    {
        $normalized = mb_strtolower((string) $this->clean($value));

        return $normalized === 'chauffeur' || $normalized === 'driver' || $normalized === 'yes' || $normalized === 'ja';
    }

    private function parseDate(string $value): ?Carbon
    {
        try {
            return Carbon::createFromFormat(self::DATE_FORMAT, $value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    private function parseDateTime(Carbon $date, string $time): ?Carbon
    {
        try {
            return Carbon::parse($date->format('Y-m-d').' '.$time);
        } catch (Throwable) {
            return null;
        }
    }

    private function isImportComplete(PlanningImport $import): bool
    {
        return ! PlanningShift::query()
            ->where('planning_import_id', $import->id)
            ->whereDoesntHave('assignments')
            ->exists();
    }

    private function findLatestStoredCsvPath(): ?string
    {
        $csvFiles = collect(Storage::disk('local')->files('csv'))
            ->filter(fn (string $file): bool => str_ends_with(mb_strtolower($file), '.csv'))
            ->sort()
            ->values();

        return $csvFiles->last();
    }

    /**
     * @return array<int, string>
     */
    private function findPendingStoredCsvPaths(): array
    {
        return collect(Storage::disk('local')->files('csv'))
            ->filter(fn (string $file): bool => str_ends_with(mb_strtolower($file), '.csv'))
            ->filter(fn (string $file): bool => ! str_starts_with(basename($file), self::IMPORTED_PREFIX))
            ->sort()
            ->values()
            ->all();
    }

    private function markStoredFileAsImported(string $storedPath): void
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($storedPath)) {
            return;
        }

        $directory = trim(dirname($storedPath), '.');
        $baseName = basename($storedPath);

        if (str_starts_with($baseName, self::IMPORTED_PREFIX)) {
            return;
        }

        $targetBaseName = self::IMPORTED_PREFIX.$baseName;
        $targetPath = ($directory !== '' ? $directory.'/' : '').$targetBaseName;

        if ($disk->exists($targetPath)) {
            $targetBaseName = self::IMPORTED_PREFIX.now()->format('YmdHisv').'_'.$baseName;
            $targetPath = ($directory !== '' ? $directory.'/' : '').$targetBaseName;
        }

        $disk->move($storedPath, $targetPath);
    }
}
