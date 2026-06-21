<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);

// Focus on uploadPdf body (lines 167-520, 0-indexed: 166-519)
$depth = 2; // class { + function {
$inSingle = false;
$inDouble = false;
$escape = false;

for ($n = 166; $n <= 519; $n++) {
    $line = $lines[$n];
    $printed = false;
    
    for ($i = 0; $i < strlen($line); $i++) {
        $ch = $line[$i];
        
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; continue; }
        if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
        if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
        if ($inSingle || $inDouble) continue;
        
        if ($ch === '{') { 
            $depth++; 
            if (!$printed) { echo "Line " . ($n+1) . " (depth $depth before): $line\n"; $printed = true; }
        }
        if ($ch === '}') { 
            $depth--; 
            if (!$printed) { echo "Line " . ($n+1) . " (depth $depth after): $line\n"; $printed = true; }
        }
    }
}

echo "\nDepth after uploadPdf body: $depth\n";
echo "Expected depth after function close: 1\n";
