<?php

namespace App\Console;

use App\Models\ImportJob;
use App\Jobs\ProcessBtebDriveImport;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$job = ImportJob::create([
    'drive_url' => 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82',
    'semester' => 'auto',
    'regulation' => 'auto',
    'holding_year' => 'auto',
    'status' => 'pending',
]);

echo "Job ID: " . $job->id . PHP_EOL;
echo "Starting import..." . PHP_EOL;

try {
    (new ProcessBtebDriveImport($job, $job->drive_url, $job->semester, $job->regulation, $job->holding_year))->handle();
    echo "Import completed!" . PHP_EOL;
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
