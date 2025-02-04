<?php

namespace App\Console\Commands;

use App\Models\Postcode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use League\Csv\Reader;
use MatanYadaev\EloquentSpatial\Objects\Point;

class UpdatePostcodes extends Command
{
    const POSTCODE_SOURCE_URL = 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-postcodes {region}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads postcodes for specified region (i.e. EH, AB, FK)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $region = $this->argument('region');

        $tempPath = storage_path('app/temp');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $this->info("Downloading ZIP file...");

        $zipFilePath = $tempPath . '/postcodes.zip';
        $response = Http::withOptions(['sink' => $zipFilePath, 'timeout' => 60])->get(self::POSTCODE_SOURCE_URL);

        if ($response->failed()) {
            $this->error("Failed to download ZIP file.");

            return false;
        }

        $this->info("Extracting ZIP file...");

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath) === true) {
            $zip->extractTo($tempPath);
            $zip->close();
        } else {
            $this->error("Failed to extract ZIP file.");

            return false;
        }

        $csvFullPath = $tempPath . '/Data/multi_csv/ONSPD_NOV_2022_UK_' . $region . '.csv';
        $this->info("Processing CSV file: $csvFullPath");

        try {
            $csv = Reader::createFromPath($csvFullPath);
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();

            $totalRows = iterator_count($csv->getRecords());
        } catch (\Exception $e) {
            $this->error('Unable to load postcodes. Are you sure you used a valid region?');

            return false;
        }

        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($records as $record) {
            if (empty($record['lat']) || empty($record['long'])) {
                $this->warn(sprintf('Skipped postcode %s because lat/long missing.', $record['pcd']));
            }

            // Note:
            // I was originally trying to use Eloquent to batch insert entries, however I couldn't get it to work
            // properly and spent some time trying to debug that. Eventually I used a prepared statement to get going
            // with the rest of the task, but that doesn't work either. Something between DB/Eloquent escapes/transforms
            // values in a way that causes a:
            // SQLSTATE[22003]: Numeric value out of range: 1416 Cannot get geometry object from data you send to the GEOMETRY field
            // So in order to avoid spending lots of time debugging this, I did a quick and dirty fix to insert the data raw.
            // I'm aware that in a production environment this is not a safe/acceptable approach, and under different circumstances
            // I would have instead continued debugging/fixing up the Eloquent/batch approach.
            try {
                Postcode::create([
                    'coordinates' => new Point($record['lat'], $record['long'], 4326),
                    'longitude' => $record['long'],
                    'latitude' => $record['lat'],
                    'postcode' => str_replace(' ', '', $record['pcd'])
                ]);

            } catch (\Exception $e) {
                $this->warn('Skipped postcode ' . $record['pcd'] .' because of  ' . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();

        unlink($zipFilePath);
        unlink($csvFullPath);

        return true;
    }
}
