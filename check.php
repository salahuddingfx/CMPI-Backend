<?php
$f = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$depth = 0;
$lines = explode("\n", $f);
foreach ($lines as $i => $l) {
    $opens = substr_count($l, '{') - substr_count($l, '}');
    $newDepth = $depth + $opens;
    if ($depth === 0 && $opens < 0) {
        echo "Line " . ($i + 1) . ": extra } found\n$l\n";
    }
    $depth = $newDepth;
    if ($depth < 0) {
        echo "Line " . ($i + 1) . ": depth went negative: $depth\n";
    }
}
echo "Final depth: $depth\n";
