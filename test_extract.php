<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$job = \App\Models\ImportJob::create([
    'drive_url' => 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82',
    'semester' => 'auto',
    'regulation' => 'auto',
    'holding_year' => 'auto',
    'status' => 'pending',
]);

$ref = new \ReflectionClass(\App\Jobs\ProcessBtebDriveImport::class);
$importer = $ref->newInstance($job, $job->drive_url, $job->semester, $job->regulation, $job->holding_year);

$method = $ref->getMethod('extractFileIds');
$method->setAccessible(true);

try {
    $result = $method->invoke($importer, $job->drive_url);
    echo "Files found: " . count($result['ids']) . PHP_EOL;
    if (count($result['ids']) > 0) {
        echo "First 5 IDs: " . implode(', ', array_slice($result['ids'], 0, 5)) . PHP_EOL;
        echo "Folders: " . PHP_EOL;
        $uniqueFolders = array_unique(array_values($result['file_folders']));
        foreach ($uniqueFolders as $folder) {
            echo "  - $folder" . PHP_EOL;
        }
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
