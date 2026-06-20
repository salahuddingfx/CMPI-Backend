<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Smalot\PdfParser\Parser;

$parser = new Parser();

$tests = [
    'IRR_8th_2016' => '1MerZtBJSzZeEfP39yCGNngtniH5zseRE',
    'ALLIED_1ST' => '1NgC9B3wsicgwNG1GQh4saLaJp9tCO2XY',
];

foreach ($tests as $label => $fid) {
    $url = "https://drive.google.com/uc?export=download&id={$fid}";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if (strpos($response, '%PDF') !== 0) {
        echo "{$label}: Not a PDF" . PHP_EOL;
        continue;
    }

    $pdf = $parser->parseContent($response);
    echo "=== {$label} === (pages: " . count($pdf->getPages()) . ")" . PHP_EOL;
    foreach ($pdf->getPages() as $i => $page) {
        $text = $page->getText();
        echo "Page " . ($i+1) . " (first 1500 chars):" . PHP_EOL;
        echo substr($text, 0, 1500) . PHP_EOL . PHP_EOL;
    }
}
