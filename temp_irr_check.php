<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Check if irregular PDFs were downloaded - look at regulation patterns
$regs = DB::select("SELECT regulation, COUNT(DISTINCT roll) as rolls, COUNT(*) as records FROM bteb_results GROUP BY regulation ORDER BY regulation");
echo "By regulation:" . PHP_EOL;
foreach ($regs as $r) {
    echo "  Reg {$r->regulation}: {$r->rolls} rolls, {$r->records} records" . PHP_EOL;
}

// Check ALLIED data (also special)
$allied = DB::select("SELECT semester, COUNT(DISTINCT roll) as rolls, COUNT(*) as records FROM bteb_results WHERE semester LIKE '%ALLIED%' OR raw_text LIKE '%ALLIED%' GROUP BY semester");
echo PHP_EOL . "ALLIED data:" . PHP_EOL;
foreach ($allied as $a) {
    echo "  {$a->semester}: {$a->rolls} rolls, {$a->records} records" . PHP_EOL;
}

// Check what semesters exist
$sems = DB::select("SELECT semester, COUNT(DISTINCT roll) as rolls, COUNT(*) as records FROM bteb_results GROUP BY semester ORDER BY FIELD(semester, '1st','2nd','3rd','4th','5th','6th','7th','8th')");
echo PHP_EOL . "By semester:" . PHP_EOL;
foreach ($sems as $s) {
    echo "  {$s->semester}: {$s->rolls} rolls, {$s->records} records" . PHP_EOL;
}
