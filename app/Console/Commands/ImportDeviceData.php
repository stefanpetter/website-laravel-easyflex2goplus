<?php

namespace App\Console\Commands;

use App\Models\Bed;
use App\Models\House;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDeviceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:device-data {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import device_id and device_source data from energieoranje and energierapportit databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        $this->info('Starting device data import...');

        $sources = [
            ['connection' => 'energieoranje', 'source' => 'oranje'],
            ['connection' => 'energierapportit', 'source' => 'rapportit'],
        ];

        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalNotFound = 0;

        // Get the default connection name
        $defaultConnection = config('database.default');

        foreach ($sources as $sourceConfig) {
            $connection = $sourceConfig['connection'];
            $source = $sourceConfig['source'];

            $this->info("\nProcessing {$source} ({$connection})...");

            try {
                // Get all devices from the external database and convert to array
                $devices = DB::connection($connection)
                    ->table('devices')
                    ->select('id', 'address_street', 'address_number')
                    ->whereNotNull('address_street')
                    ->whereNotNull('address_number')
                    ->get()
                    ->toArray();

                $this->info("Found " . count($devices) . " devices in {$source}");

                foreach ($devices as $device) {
                    // Construct the house name from address
                    $houseName = trim($device->address_street . ' ' . $device->address_number);

                    // Find the house in our database using the default connection
                    $house = House::on($defaultConnection)->where('name', $houseName)->first();

                    if (!$house) {
                        $this->warn("House not found: {$houseName}");
                        $totalNotFound++;
                        continue;
                    }

                    // Get all beds for this house through rooms using the default connection
                    $beds = Bed::on($defaultConnection)->whereHas('room', function ($query) use ($house) {
                        $query->where('house_id', $house->id);
                    })->get();

                    if ($beds->isEmpty()) {
                        $this->warn("No beds found for house: {$houseName}");
                        continue;
                    }

                    // Update beds with device info
                    foreach ($beds as $bed) {
                        if ($bed->device_id && $bed->device_source) {
                            $this->line("Skipping bed {$bed->id} (already has device data)");
                            $totalSkipped++;
                            continue;
                        }

                        if (!$dryRun) {
                            $bed->update([
                                'device_id' => (string) $device->id,
                                'device_source' => $source,
                            ]);
                        }

                        $this->info("Updated bed {$bed->id} ({$bed->name}) in {$houseName} with device {$device->id} from {$source}");
                        $totalUpdated++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error processing {$source}: " . $e->getMessage());
                continue;
            }
        }

        $this->newLine();
        $this->info('Import completed!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $totalUpdated],
                ['Skipped (already has data)', $totalSkipped],
                ['Houses not found', $totalNotFound],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN - No changes were made. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}
