<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ImportJob;
use App\Jobs\ProcessBtebDriveImport;

$job = new ImportJob();
$job->drive_url = 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82';
$job->semester = 'auto';
$job->regulation = 'auto';
$job->holding_year = 'auto';
$job->status = 'pending';
$job->save();

ProcessBtebDriveImport::dispatch($job, $job->drive_url);
echo "Job dispatched: {$job->id}\n";
echo "Run: php artisan queue:work --max-jobs=1 --timeout=600\n";
