<?php

namespace App\Console\Commands;

use App\Models\ImportJob;
use App\Jobs\ProcessBtebDriveImport;
use Illuminate\Console\Command;

class ImportBtebResults extends Command
{
    protected $signature = 'bteb:import-drive {driveUrl?} {--resume} {--holding-year=}';
    protected $description = 'Import BTEB results from Google Drive folder';

    public function handle(): int
    {
        $resume = $this->option('resume');
        $holdingYear = $this->option('holding-year') ?: '2025';

        if ($resume) {
            $lastJob = ImportJob::where('status', 'processing')->latest()->first();
            if (!$lastJob) {
                $this->error("No processing job found to resume.");
                return Command::FAILURE;
            }
            $this->info("Resuming job #{$lastJob->id} from: {$lastJob->drive_url}");
            $this->info("Already processed: {$lastJob->processed_files}/{$lastJob->total_files} files, {$lastJob->total_results} results");

            $job = new ProcessBtebDriveImport($lastJob, $lastJob->drive_url, null, null, $lastJob->holding_year);
            $job->handle();
            $lastJob->refresh();
            $this->printSummary($lastJob);
            return Command::SUCCESS;
        }

        $driveUrl = $this->argument('driveUrl') ?: 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82';

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
        $this->printSummary($importJob);

        return Command::SUCCESS;
    }

    private function printSummary(ImportJob $job): void
    {
        $this->newLine();
        $this->info("Import completed!");
        $this->info("Status: {$job->status}");
        $this->info("Total files: {$job->total_files}");
        $this->info("Processed: {$job->processed_files}");
        $this->info("Results imported: {$job->total_results}");

        if ($job->error_log) {
            $this->warn("Errors: " . json_encode($job->error_log));
        }
    }
}
