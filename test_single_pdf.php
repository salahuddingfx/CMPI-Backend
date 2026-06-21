<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use App\Utils\BtebSubjectSemesterMap;

$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

// Step 1: Get file IDs from Google Drive folder
echo "=== Step 1: Fetching Google Drive folder ===\n";
$url = "https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82";
$response = Http::withHeaders([
    'User-Agent' => $ua,
    'Accept' => 'text/html,application/xhtml+xml',
])->timeout(20)->get($url);

$html = $response->body();
if (!preg_match('/window\[\'_DRIVE_ivd\'\]\s*=\s*\'(.+?)\'\s*;/s', $html, $match)) {
    echo "ERROR: Could not parse Google Drive folder HTML\n";
    exit(1);
}

$raw = $match[1];
$decoded = preg_replace_callback('/\\\\x([0-9a-fA-F]{2})/', function ($m) {
    return chr(hexdec($m[1]));
}, $raw);

$json = json_decode($decoded, true);
$items = $json[0] ?? $json;

$files = [];
$folders = [];
foreach ($items as $item) {
    if (!is_array($item) || count($item) < 4) continue;
    $id = $item[0] ?? null;
    $name = $item[2] ?? '';
    $mimeType = $item[3] ?? '';
    if (!is_string($id) || strlen($id) < 20) continue;
    
    if (strpos($mimeType, 'folder') !== false) {
        $folders[$id] = $name;
        echo "  FOLDER: $name (ID: $id)\n";
    } else {
        $files[$id] = $name;
        echo "  FILE: $name (ID: $id)\n";
    }
}

echo "\nFiles: " . count($files) . ", Folders: " . count($folders) . "\n\n";

// Step 2: Pick a specific file to test — let's find one that should have 2nd sem data
// Let's download the FIRST file and parse it
$testFileId = array_key_first($files);
$testFileName = $files[$testFileId];
echo "=== Step 2: Downloading test file: $testFileName ===\n";

$dlUrl = "https://drive.google.com/uc?export=download&id={$testFileId}";
$dlResponse = Http::timeout(90)->withHeaders(['User-Agent' => $ua])->get($dlUrl);
$body = $dlResponse->body();

// Check for virus scan warning
if (strpos($body, 'confirm=') !== false || strpos($body, 'download_warning') !== false) {
    if (preg_match('/confirm=([0-9A-Za-z_-]+)/', $body, $confirmMatch)) {
        $confirmUrl = "https://drive.google.com/uc?export=download&confirm={$confirmMatch[1]}&id={$testFileId}";
        $dlResponse = Http::timeout(90)->withHeaders(['User-Agent' => $ua])->get($confirmUrl);
        $body = $dlResponse->body();
    }
}

if (strpos($body, '%PDF') !== 0) {
    echo "ERROR: Not a valid PDF (starts with: " . substr($body, 0, 50) . ")\n";
    // Try direct download
    $directUrl = "https://drive.usercontent.google.com/download?id={$testFileId}&export=download";
    $dlResponse = Http::timeout(90)->withHeaders(['User-Agent' => $ua])->get($directUrl);
    $body = $dlResponse->body();
    if (strpos($body, '%PDF') !== 0) {
        echo "Still not PDF. Trying with confirm...\n";
        if (preg_match('/confirm=([0-9A-Za-z_-]+)/', $body, $cm)) {
            $u2 = "https://drive.usercontent.google.com/download?id={$testFileId}&export=download&confirm={$cm[1]}";
            $dlResponse = Http::timeout(90)->withHeaders(['User-Agent' => $ua])->get($u2);
            $body = $dlResponse->body();
        }
    }
}

if (strpos($body, '%PDF') !== 0) {
    echo "FAILED to download valid PDF\n";
    echo "Response length: " . strlen($body) . "\n";
    echo "First 200 chars: " . substr($body, 0, 200) . "\n";
    exit(1);
}

$tmpFile = tempnam(sys_get_temp_dir(), 'bteb_');
file_put_contents($tmpFile, $body);
echo "Downloaded: " . round(strlen($body) / 1024, 1) . " KB\n";

// Step 3: Parse the PDF
echo "\n=== Step 3: Parsing PDF ===\n";
$parser = new Parser();
$pdf = $parser->parseContent($body);
$pages = $pdf->getPages();
echo "Total pages: " . count($pages) . "\n";

// Step 4: Check what CMPI data we extract
echo "\n=== Step 4: Extracting CMPI students ===\n";
$allResults = [];
$lastDetectedDept = "Computer Science & Technology";

foreach ($pages as $pageIndex => $page) {
    $pageText = $page->getText();
    
    // Check for CMPI center codes
    $hasCmpi = (strpos($pageText, '74026') !== false || strpos($pageText, '16058') !== false || strpos($pageText, '51020') !== false);
    
    if ($hasCmpi) {
        // Count rolls on this page
        preg_match_all('/\b\d{5,6}\b/', $pageText, $allRolls);
        $uniqueRolls = array_unique($allRolls[0]);
        
        echo "  Page " . ($pageIndex + 1) . ": CMPI YES, " . count($uniqueRolls) . " unique rolls found\n";
        
        // Show first few rolls
        $sample = array_slice($uniqueRolls, 0, 5);
        echo "    Sample rolls: " . implode(', ', $sample) . "\n";
    }
}

// Step 5: Now parse properly and see how many records we'd get
echo "\n=== Step 5: Full parse ===\n";
$totalParsed = 0;
foreach ($pages as $page) {
    $pageText = $page->getText();
    $results = (new \App\Jobs\ProcessBtebDriveImport(
        new \App\Models\ImportJob(['id' => 999]),
        'test',
        'auto',
        'auto',
        'auto'
    ))->parsePdfText($pageText, 'auto', 'auto', date('Y'), $lastDetectedDept, false);
    
    if (!empty($results)) {
        $totalParsed += count($results);
        foreach ($results as $r) {
            $allResults[] = $r;
        }
    }
}
echo "Total parsed results: $totalParsed\n";

// Step 6: Check DB for same file's rolls
echo "\n=== Step 6: Compare with DB ===\n";
if (!empty($allResults)) {
    $parsedRolls = array_unique(array_column($allResults, 'roll'));
    echo "Parsed rolls: " . count($parsedRolls) . "\n";
    
    // Check which are already in DB
    $existingInDb = DB::table('bteb_results')
        ->whereIn('roll', $parsedRolls)
        ->distinct()
        ->pluck('roll');
    echo "Already in DB: " . count($existingInDb) . "\n";
    
    $newRolls = array_diff($parsedRolls, $existingInDb->toArray());
    echo "NEW rolls (not in DB): " . count($newRolls) . "\n";
    if (!empty($newRolls)) {
        echo "  New: " . implode(', ', array_slice(array_values($newRolls), 0, 10)) . "\n";
    }
    
    // Check per-semester breakdown
    echo "\nParsed semester breakdown:\n";
    $semCounts = array_count_values(array_column($allResults, 'semester'));
    ksort($semCounts);
    foreach ($semCounts as $sem => $cnt) {
        echo "  $sem: $cnt\n";
    }
    
    // Compare with DB semester counts for these rolls
    echo "\nDB semester counts for same rolls:\n";
    $dbCounts = DB::table('bteb_results')
        ->whereIn('roll', $parsedRolls)
        ->select('semester', DB::raw('count(*) as cnt'))
        ->groupBy('semester')
        ->orderBy('semester')
        ->get();
    foreach ($dbCounts as $d) {
        echo "  {$d->semester}: {$d->cnt}\n";
    }
}

unlink($tmpFile);
echo "\n=== Done ===\n";
