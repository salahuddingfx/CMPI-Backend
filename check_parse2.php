<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Find uploadPdf
$start = strpos($code, 'public function uploadPdf');
$bracePos = strpos($code, '{', $start);

$depth = 0;
$inSingle = false;
$inDouble = false;
$escape = false;
$line = 1;
$lastLine = 1;
for ($i = $bracePos; $i < strlen($code); $i++) {
    $ch = $code[$i];
    if ($ch === "\n") { $line++; continue; }
    
    if ($escape) { $escape = false; continue; }
    if ($ch === '\\') { $escape = true; continue; }
    
    if ($ch === "'" && !$inDouble) $inSingle = !$inSingle;
    if ($ch === '"' && !$inSingle) $inDouble = !$inDouble;
    
    if ($inSingle || $inDouble) continue;
    
    if ($ch === '{') {
        $depth++;
        if ($depth === 1) echo "Open func body at line " . ($line + 165) . "\n";
    }
    if ($ch === '}') {
        $depth--;
        if ($depth === 0) {
            echo "Close func body at line " . ($line + 165) . "\n";
            break;
        }
    }
}
echo "Depth at end: $depth\n";
