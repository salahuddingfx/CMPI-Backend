<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);
$depth = 0;
$inString = false;
$stringChar = null;
foreach ($lines as $i => $l) {
    $lineNum = $i + 1;
    $stripped = $l;
    // Simple approach: remove string contents
    $clean = '';
    $len = strlen($l);
    $inS = false;
    $sChar = null;
    $escape = false;
    for ($p = 0; $p < $len; $p++) {
        $ch = $l[$p];
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\') { $escape = true; continue; }
        if ($inS) {
            if ($ch === $sChar) { $inS = false; }
            continue;
        }
        if ($ch === '"' || $ch === "'") { $inS = true; $sChar = $ch; continue; }
        $clean .= $ch;
    }
    // Also remove regex patterns (between /)
    $clean = preg_replace('/\/(?:[^\/\\\\]|\\\\.)+\/[msixu]*/', '', $clean);
    
    $opens = substr_count($clean, '{');
    $closes = substr_count($clean, '}');
    $depth += $opens - $closes;
    
    if ($depth === 0 && $closes > $opens && $lineNum < 724) {
        echo "Possible early class close at line $lineNum: $l\n";
    }
}
echo "Depth at end: $depth\n";
