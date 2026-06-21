<?php
function stripStrings($code) {
    $result = '';
    $len = strlen($code);
    $inSingle = false;
    $inDouble = false;
    $escape = false;
    for ($i = 0; $i < $len; $i++) {
        $ch = $code[$i];
        if ($escape) { $escape = false; $result .= $ch; continue; }
        if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; $result .= $ch; continue; }
        if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; $result .= $ch; continue; }
        if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; $result .= $ch; continue; }
        if ($inSingle || $inDouble) { if ($ch === "\n") $result .= "\n"; continue; }
        if ($ch === '{') $result .= '{';
        elseif ($ch === '}') $result .= '}';
        else $result .= ' ';
    }
    return $result;
}

// Get current and original files
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$origCode = file_get_contents(__DIR__ . '/original_controller.php');

// Create brace-only versions
$braceCurrent = preg_replace('/[^{}]/', '', stripStrings($code));
$braceOrig = preg_replace('/[^{}]/', '', stripStrings($origCode));

echo "Current file: " . strlen($braceCurrent) . " braces, net: " . (substr_count($braceCurrent, '{') - substr_count($braceCurrent, '}')) . "\n";
echo "Original file: " . strlen($braceOrig) . " braces, net: " . (substr_count($braceOrig, '{') - substr_count($braceOrig, '}')) . "\n";

// Find the first difference in the brace sequence
$minLen = min(strlen($braceCurrent), strlen($braceOrig));
for ($i = 0; $i < $minLen; $i++) {
    if ($braceCurrent[$i] !== $braceOrig[$i]) {
        echo "First brace mismatch at position $i\n";
        echo "Current around $i: " . substr($braceCurrent, max(0, $i-5), 15) . "\n";
        echo "Original around $i: " . substr($braceOrig, max(0, $i-5), 15) . "\n";
        
        // Map position back to line number
        $stripped = stripStrings($code);
        $pos = 0;
        for ($j = 0; $j < strlen($stripped); $j++) {
            if ($stripped[$j] === '{' || $stripped[$j] === '}') {
                if ($pos === $i) {
                    echo "In current file: character position $j\n";
                    // Count lines up to position j
                    $lineNum = 1;
                    for ($k = 0; $k < $j; $k++) {
                        if ($stripped[$k] === "\n") $lineNum++;
                    }
                    echo "Around line $lineNum in current file\n";
                    break;
                }
                $pos++;
            }
        }
        break;
    }
}

echo "\nTotal current braces: " . substr_count($braceCurrent, '{') . " {, " . substr_count($braceCurrent, '}') . " }\n";
echo "Total original braces: " . substr_count($braceOrig, '{') . " {, " . substr_count($braceOrig, '}') . " }\n";

// Strip all string contents to count only structural braces
$stripped = '';
$len = strlen($code);
$inSingle = false;
$inDouble = false;
$escape = false;
$curlyCount = 0;

for ($i = 0; $i < $len; $i++) {
    $ch = $code[$i];
    
    if ($escape) { $escape = false; if ($inDouble) { $stripped .= $ch; } continue; }
    if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; if ($inDouble) { $stripped .= $ch; } continue; }
    
    if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; if (!$inSingle) continue; }
    if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; if (!$inDouble) continue; }
    
    if ($inSingle) continue;
    if ($inDouble) {
        // Track ${...} and {$...} inside double-quoted strings
        if ($ch === '{' || $ch === '}') {
            // Inside a double-quoted string, these are interpolation
            continue; // skip them
        }
        continue;
    }
    
    if ($ch === '{') {
        $curlyCount++;
        $stripped .= '{';
    } elseif ($ch === '}') {
        $curlyCount--;
        $stripped .= '}';
    }
}

echo "Structural brace difference: " . $curlyCount . "\n";

// Find the exact lines of unmatched braces
$sLines = explode("\n", $stripped);
$depth = 0;
$minDepth = 0;
for ($n = 0; $n < count($sLines); $n++) {
    $line = $sLines[$n];
    for ($i = 0; $i < strlen($line); $i++) {
        if ($line[$i] === '{') {
            $depth++;
            if ($depth > 0 && $depth > count($sLines)) break;
        }
        if ($line[$i] === '}') {
            $depth--;
            if ($depth < $minDepth) {
                $minDepth = $depth;
                echo "Unmatched } at structural line " . ($n + 1) . ", depth=$depth\n";
            }
        }
    }
}
echo "Final depth: $depth\n";
if ($depth > 0) {
    // Find where the extra { is
    $depth = 0;
    for ($n = 0; $n < count($sLines); $n++) {
        $line = $sLines[$n];
        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] === '{') { $depth++; }
            if ($line[$i] === '}') { $depth--; }
        }
        if ($depth === 1) {
            echo "Depth returns to 1 at structural line " . ($n + 1) . "\n";
            break;
        }
        if ($depth > 1) {
            echo "Depth is $depth at structural line " . ($n + 1) . "\n";
        }
    }
}
