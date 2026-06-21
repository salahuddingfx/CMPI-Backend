<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== Google Drive Files We Processed ===\n\n";

// Check import job history
$jobs = DB::table('jobs')->get();
echo "Jobs in queue: " . count($jobs) . "\n\n";

// Check failed_jobs
$failed = DB::table('failed_jobs')->count();
echo "Failed jobs: $failed\n\n";

// The real question: which semester/regulation combos exist in our data?
echo "=== Semester + Regulation combos in DB ===\n";
DB::table('bteb_results')
    ->select('semester', 'regulation', 'department', DB::raw('count(DISTINCT roll) as students'), DB::raw('count(*) as records'))
    ->groupBy('semester', 'regulation', 'department')
    ->orderBy('semester')
    ->orderBy('regulation')
    ->orderBy('department')
    ->get()
    ->each(function($r) {
        echo "  {$r->semester} sem | Reg {$r->regulation} | {$r->department}: {$r->students} students, {$r->records} records\n";
    });

// Which Google Drive files were downloaded? Let's check the filesystem
echo "\n=== Downloaded Drive Files ===\n";
$driveDir = __DIR__ . '/storage/app/google-drive';
if (is_dir($driveDir)) {
    $files = scandir($driveDir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $driveDir . '/' . $f;
        if (is_file($path)) {
            $size = round(filesize($path) / 1024, 1);
            echo "  $f ($size KB)\n";
        } elseif (is_dir($path)) {
            echo "  [$f/]\n";
            $subfiles = scandir($path);
            foreach ($subfiles as $sf) {
                if ($sf === '.' || $sf === '..') continue;
                $sPath = $path . '/' . $sf;
                if (is_file($sPath)) {
                    $size = round(filesize($sPath) / 1024, 1);
                    echo "    $sf ($size KB)\n";
                }
            }
        }
    }
} else {
    echo "  Directory not found: $driveDir\n";
}
