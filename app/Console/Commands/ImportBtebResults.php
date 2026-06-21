<?php

namespace App\Console\Commands;

use App\Models\ImportJob;
use App\Jobs\ProcessBtebDriveImport;
use Illuminate\Console\Command;

class ImportBtebResults extends Command
{
    protected $signature = 'bteb:import-drive {driveUrl} {--holding-year=}';
    protected $description = 'Import BTEB results from Google Drive folder';

    public function handle(): int
    {
        $driveUrl = $this->argument('driveUrl');
        $holdingYear = $this->option('holding-year') ?: '2025';

        $this->info("Starting import from: {$driveUrl}");
        $this->info("Holding year: {$holdingYear}");

        $importJob = ImportJob::create([
            'drive_url' => $driveUrl,
            'holding_year' => $holdingYear,
            'status' => 'pending',
        ]);

        $job = new ProcessBtebDriveImport($importJob, $driveUrl, null, null, $holdingYear);
        $job->handle();

        $importJob->refresh();

        $this->newLine();
        $this->info("Import completed!");
        $this->info("Status: {$importJob->status}");
        $this->info("Total files: {$importJob->total_files}");
        $this->info("Processed: {$importJob->processed_files}");
        $this->info("Results imported: {$importJob->total_results}");

        if ($importJob->error_log) {
            $this->warn("Errors: " . json_encode($importJob->error_log));
        }

        return Command::SUCCESS;
    }
}
