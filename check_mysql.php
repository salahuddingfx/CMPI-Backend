<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $rows = DB::select('SHOW FULL PROCESSLIST');
    foreach ($rows as $r) {
        echo "{$r->Id} | {$r->Command} | {$r->Time}s | " . substr($r->Info ?? '', 0, 100) . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
