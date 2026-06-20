<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BtebResult;

$semesters = BtebResult::selectRaw('semester, regulation, count(*) as cnt')
    ->groupBy('semester', 'regulation')
    ->orderBy('semester')
    ->get();

foreach ($semesters as $s) {
    echo $s->semester . ' | ' . $s->regulation . ' | ' . $s->cnt . " records\n";
}

echo "\nTotal: " . BtebResult::count() . "\n";
echo "Distinct rolls: " . BtebResult::distinct('roll')->count('roll') . "\n";
