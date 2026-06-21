<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = App\Models\BtebResult::where('exam_type', 'rescrutiny')->get();
echo "Found " . $results->count() . " rescrutiny records.\n";
foreach ($results->take(5) as $r) {
    // Find regular record too
    $reg = App\Models\BtebResult::where('roll', $r->roll)->where('semester', $r->semester)->where('exam_type', 'regular')->first();
    echo "Roll: " . $r->roll . " | Semester: " . $r->semester . " | Challenge GPA: " . $r->gpa . " | Regular GPA: " . ($reg ? $reg->gpa : 'None') . "\n";
}
