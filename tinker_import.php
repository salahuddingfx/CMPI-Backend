$job = \App\Models\ImportJob::create([
    'drive_url' => 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82',
    'semester' => 'auto',
    'regulation' => 'auto',
    'holding_year' => 'auto',
    'status' => 'pending',
]);
echo "Job ID: " . $job->id . "\n";
echo "Starting import...\n";
(new \App\Jobs\ProcessBtebDriveImport($job, $job->drive_url, $job->semester, $job->regulation, $job->holding_year))->handle();
echo "Import completed!\n";
