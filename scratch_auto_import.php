<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

$driveUrl = "https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82";

echo "Fetching Google Drive folder content...\n";
$response = Http::get($driveUrl);
if (!$response->successful()) {
    die("Failed to fetch Google Drive folder.\n");
}
$html = $response->body();

preg_match('/folders\/([a-zA-Z0-9_-]{33})/', $driveUrl, $folderMatch);
$folderId = $folderMatch[1] ?? null;

preg_match_all('/"(1[a-zA-Z0-9_-]{32})"/', $html, $matches);
$ids = $matches[1] ?? [];
if (empty($ids)) {
    preg_match_all('/\'(1[a-zA-Z0-9_-]{32})\'/', $html, $matches);
    $ids = $matches[1] ?? [];
}
if (empty($ids)) {
    preg_match_all('/\b(1[a-zA-Z0-9_-]{32})\b/', $html, $matches);
    $ids = $matches[1] ?? [];
}

$fileIds = array_values(array_unique(array_filter($ids)));
echo "Found " . count($fileIds) . " files.\n";

$pdfParser = new Parser();
foreach ($fileIds as $idx => $fileId) {
    if ($fileId === $folderId) continue;
    echo "\n-------------------------------------\n";
    echo "Processing File " . ($idx + 1) . " (ID: $fileId):\n";
    try {
        $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId . "&confirm=no_antivirus";
        $fileResponse = Http::timeout(60)->get($downloadUrl);
        if (!$fileResponse->successful()) {
            echo "Failed to download file.\n";
            continue;
        }

        $pdfContent = $fileResponse->body();
        if (strpos($pdfContent, '%PDF') !== 0) {
            echo "Not a valid PDF.\n";
            continue;
        }

        $pdf = $pdfParser->parseContent($pdfContent);
        $pages = $pdf->getPages();
        echo "Total Pages: " . count($pages) . "\n";

        if (count($pages) > 0) {
            $firstPageText = $pages[0]->getText();
            echo "First 150 chars of page 1:\n";
            echo substr(preg_replace('/\s+/', ' ', $firstPageText), 0, 150) . "\n";

            // Semester
            $detectedSem = "Unknown";
            foreach ($pages as $p) {
                $pageText = $p->getText();
                if (preg_match('/\b([1-8])(st|nd|rd|th)\s+Semester/i', $pageText, $semMatches)) {
                    $detectedSem = $semMatches[1] . strtolower($semMatches[2]);
                    break;
                }
            }
            echo "Detected Semester: $detectedSem\n";

            // Regulation
            $detectedReg = "Unknown";
            foreach ($pages as $p) {
                $pageText = $p->getText();
                if (preg_match('/(2010|2016|2022)\s+Regulation/i', $pageText, $regMatches)) {
                    $detectedReg = $regMatches[1];
                    break;
                } elseif (preg_match('/Regulation\s*-?\s*\(?\s*(2010|2016|2022)\)?/i', $pageText, $regMatches)) {
                    $detectedReg = $regMatches[1];
                    break;
                }
            }
            echo "Detected Regulation: $detectedReg\n";
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
    }
}
