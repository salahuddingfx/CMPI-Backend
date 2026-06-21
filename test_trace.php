<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Print each line with its brace depth
$lines = explode("\n", $code);
$depth = 0;
$inSingle = false;
$inDouble = false;
$escape = false;

for ($n = 0; $n < count($lines); $n++) {
    $line = $lines[$n];
    $lineDepth = $depth;
    $hasBrace = false;
    
    for ($i = 0; $i < strlen($line); $i++) {
        $ch = $line[$i];
        
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; continue; }
        if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
        if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
        if ($inSingle || $inDouble) continue;
        
        if ($ch === '{') { $depth++; $hasBrace = true; }
        if ($ch === '}') { $depth--; $hasBrace = true; }
    }
    
    if ($hasBrace) {
        echo "Line " . ($n + 1) . " (depth $depth): $line\n";
    }
}

echo "\nFinal depth: $depth\n";
