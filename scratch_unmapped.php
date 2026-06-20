<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Load client-side subject codes from typescript file using regex
$clientCodesContent = file_get_contents(__DIR__ . '/../client/src/utils/btebSubjectCodes.ts');
preg_match_all('/"(\d{5,6})"\s*:/', $clientCodesContent, $matches);
$mappedCodes = array_flip($matches[1] ?? []);

// Fetch unique referred subject codes in DB
$allCodes = [];
foreach(App\Models\BtebResult::whereNotNull('referred_subjects')->get() as $r) {
    $allCodes = array_merge($allCodes, $r->referred_subjects);
}
$uniqueDbCodes = array_unique($allCodes);

$unmapped = [];
foreach ($uniqueDbCodes as $code) {
    // Strip suffix if present
    $baseCode = preg_replace('/\([^)]+\)/', '', $code);
    $baseCode = trim($baseCode);
    if (!isset($mappedCodes[$baseCode])) {
        $unmapped[] = $baseCode;
    }
}

sort($unmapped);
echo "Total Unique Codes in DB: " . count($uniqueDbCodes) . "\n";
echo "Total Unmapped Codes: " . count($unmapped) . "\n";
echo "Unmapped Codes list:\n";
print_r($unmapped);
