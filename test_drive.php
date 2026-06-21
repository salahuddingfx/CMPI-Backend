<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82';

echo "Testing Drive URL access..." . PHP_EOL;

// Test direct HTTP access
try {
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ])->timeout(30)->get($url);
    
    echo "Status: " . $response->status() . PHP_EOL;
    echo "Body length: " . strlen($response->body()) . PHP_EOL;
    
    // Check for _DRIVE_ivd
    if (preg_match('/window\[\'_DRIVE_ivd\'\]\s*=\s*\'(.+?)\'\s*;/s', $response->body(), $match)) {
        echo "_DRIVE_ivd found! Length: " . strlen($match[1]) . PHP_EOL;
    } else {
        echo "_DRIVE_ivd NOT found" . PHP_EOL;
    }
    
    // Check for data-id
    preg_match_all('/data-id="([a-zA-Z0-9_-]{25,50})"/', $response->body(), $dataIdMatches);
    echo "data-id attributes: " . count($dataIdMatches[1]) . PHP_EOL;
    
    if (count($dataIdMatches[1]) > 0) {
        echo "First data-id: " . $dataIdMatches[1][0] . PHP_EOL;
    }
    
    // Check for file names
    preg_match_all('/data-tooltip="([^"]+)"/', $response->body(), $tooltipMatches);
    echo "Tooltips found: " . count($tooltipMatches[1]) . PHP_EOL;
    if (count($tooltipMatches[1]) > 0) {
        echo "Sample tooltips: " . implode(', ', array_slice($tooltipMatches[1], 0, 5)) . PHP_EOL;
    }
    
} catch (\Throwable $e) {
    echo "HTTP error: " . $e->getMessage() . PHP_EOL;
}
