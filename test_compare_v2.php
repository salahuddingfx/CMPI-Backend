<?php
function getStructuralBraces($code) {
    $braces = '';
    $len = strlen($code);
    $inSingle = false;
    $inDouble = false;
    $escape = false;
    for ($i = 0; $i < $len; $i++) {
        $ch = $code[$i];
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; continue; }
        if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
        if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
        if ($inSingle || $inDouble) continue;
        if ($ch === '{') $braces .= '{';
        if ($ch === '}') $braces .= '}';
    }
    return $braces;
}

function mapBraceIndexToLine($code, $idx) {
    $inSingle = false;
    $inDouble = false;
    $escape = false;
    $bracePos = 0;
    for ($i = 0; $i < strlen($code); $i++) {
        $ch = $code[$i];
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; continue; }
        if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
        if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
        if ($inSingle || $inDouble) continue;
        if ($ch === '{' || $ch === '}') {
            if ($bracePos === $idx) {
                $lineNum = 1;
                for ($j = 0; $j < $i; $j++) {
                    if ($code[$j] === "\n") $lineNum++;
                }
                return $lineNum;
            }
            $bracePos++;
        }
    }
    return -1;
}

$current = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$original = file_get_contents(__DIR__ . '/orig2.php');

$bCurrent = getStructuralBraces($current);
$bOrig = getStructuralBraces($original);

echo "Current: " . strlen($bCurrent) . " braces, " . substr_count($bCurrent, '{') . " {, " . substr_count($bCurrent, '}') . " }\n";
echo "Original: " . strlen($bOrig) . " braces, " . substr_count($bOrig, '{') . " {, " . substr_count($bOrig, '}') . " }\n";

// Find positions where current has extra/missing braces compared to original
$minLen = min(strlen($bCurrent), strlen($bOrig));
echo "\n--- First 10 differences ---\n";
$diffCount = 0;
for ($i = 0; $i < max(strlen($bCurrent), strlen($bOrig)) && $diffCount < 20; $i++) {
    $cChar = ($i < strlen($bCurrent)) ? $bCurrent[$i] : '(none)';
    $oChar = ($i < strlen($bOrig)) ? $bOrig[$i] : '(none)';
    if ($cChar !== $oChar) {
        $line = mapBraceIndexToLine($current, $i);
        echo "Brace idx $i: Current='$cChar' (line ~$line) vs Orig='$oChar'\n";
        $diffCount++;
    }
}
