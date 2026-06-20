<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\ProcessBtebDriveImport;
use App\Models\ImportJob;

// Create a test job for one file
$job = ImportJob::create([
    'drive_url' => 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82',
    'semester' => null,
    'regulation' => null,
    'holding_year' => null,
    'status' => 'pending',
]);

echo "Created job: {$job->id}\n";

// Dispatch it
ProcessBtebDriveImport::dispatch($job, 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82');
echo "Job dispatched. Run: php artisan queue:work --once\n";
