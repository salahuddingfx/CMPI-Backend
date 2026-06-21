<?php
$start = microtime(true);
$parser = new \Smalot\PdfParser\Parser();
$pdfDir = 'F:\CMPI\backend\storage\app\bteb_pdfs';

$files = glob($pdfDir . '/RESULT_1st_2022_Regulation.pdf');
if (empty($files)) {
    echo "No test PDF found. Let me check what we have...\n";
    $all = glob($pdfDir . '/*.pdf');
    echo "Files in dir: " . count($all) . "\n";
    foreach (array_slice($all, 0, 5) as $f) {
        echo "  " . basename($f) . " - " . round(filesize($f)/1024/1024, 1) . " MB\n";
    }
    exit;
}

$filePath = $files[0];
echo "Testing: " . basename($filePath) . " (" . round(filesize($filePath)/1024/1024, 1) . " MB)\n";

$pdf = $parser->parseFile($filePath);
$pages = $pdf->getPages();
$pageCount = count($pages);
$elapsed = round(microtime(true) - $start, 2);
echo "Pages: {$pageCount}\n";
echo "Parse time: {$elapsed}s\n";
echo "Speed: " . round($pageCount / $elapsed, 1) . " pages/sec\n";

$text = $pages[0]->getText();
echo "First page text: " . strlen($text) . " chars\n";
echo substr($text, 0, 300) . "\n";
