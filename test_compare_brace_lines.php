<?php
$current = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$orig = file_get_contents(__DIR__ . '/original_controller.php');

echo "Current file size: " . strlen($current) . " bytes\n";
echo "Original file size: " . strlen($orig) . " bytes\n";

// Count { in different contexts
function analyzeBraces($code) {
    $lines = explode("\n", $code);
    $total = 0;
    $inSingle = false;
    $inDouble = false;
    $escape = false;
    
    for ($n = 0; $n < count($lines); $n++) {
        $line = $lines[$n];
        $braceChars = '';
        for ($i = 0; $i < strlen($line); $i++) {
            $ch = $line[$i];
            if ($escape) { $escape = false; continue; }
            if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; continue; }
            if ($ch === "'" && !$inDouble) { 
                $inSingle = !$inSingle; 
                continue; 
            }
            if ($ch === '"' && !$inSingle) { 
                $inDouble = !$inDouble; 
                if ($inDouble) {
                    // After opening ", check for {$ and ${ at start
                    if ($i+1 < strlen($line) && $line[$i+1] === '{' && $line[$i+2] === '$') {
                        // This is "{$var}" - first char after string
                    }
                }
                continue; 
            }
            if ($inSingle || $inDouble) continue;
            if ($ch === '{' || $ch === '}') {
                $braceChars .= $ch;
            }
        }
        if ($braceChars !== '') {
            echo "Line " . ($n+1) . ": $braceChars\n";
        }
    }
}

echo "\n=== CURRENT FILE BRACES ===\n";
analyzeBraces($current);
echo "\n=== ORIGINAL FILE BRACES ===\n";  
analyzeBraces($orig);
