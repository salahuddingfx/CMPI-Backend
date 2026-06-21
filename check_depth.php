<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);

$depth = 0;

foreach ($lines as $i => $line) {
    $num = $i + 1;
    
    // Only count braces in non-string contexts by doing simple character scan
    $inSingle = false;
    $inDouble = false;
    $escape = false;
    $opens = 0;
    $closes = 0;
    
    for ($p = 0; $p < strlen($line); $p++) {
        $ch = $line[$p];
        
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\') { $escape = true; continue; }
        
        if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
        if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
        
        if ($inSingle || $inDouble) continue;
        
        if ($ch === '{') $opens++;
        if ($ch === '}') $closes++;
    }
    
    $depth += $opens - $closes;
    
    if ($depth < 0) {
        echo "Line $num: DEPTH NEGATIVE ($depth): $line\n";
    }
    
    if ($num >= 355 && $num <= 360) {
        echo "Line $num: depth=$depth, opens=$opens, closes=$closes: $line\n";
    }
    if ($num >= 475 && $num <= 530) {
        echo "Line $num: depth=$depth, opens=$opens, closes=$closes: $line\n";
    }
}

echo "Final depth: $depth\n";
