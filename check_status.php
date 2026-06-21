<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jobs = DB::select('SELECT id, status, total_files, processed_files, total_results, error_log FROM import_jobs ORDER BY id DESC LIMIT 2');
foreach ($jobs as $j) {
    echo "Job {$j->id}: {$j->status}, files: {$j->processed_files}/{$j->total_files}, results: {$j->total_results}" . PHP_EOL;
    if ($j->error_log) echo "Errors: {$j->error_log}" . PHP_EOL;
}

echo PHP_EOL . "Total bteb_results: " . DB::table('bteb_results')->count() . PHP_EOL;
echo "Rescrutiny: " . DB::table('bteb_results')->where('exam_type', 'rescrutiny')->count() . PHP_EOL;
