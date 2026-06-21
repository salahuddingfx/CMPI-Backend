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

$current = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$original = file_get_contents(__DIR__ . '/original_controller.php');

$bCurrent = getStructuralBraces($current);
$bOrig = getStructuralBraces($original);

echo "Current: " . strlen($bCurrent) . " braces, " . substr_count($bCurrent, '{') . " {, " . substr_count($bCurrent, '}') . " }, net=" . (substr_count($bCurrent, '{')-substr_count($bCurrent, '}')) . "\n";
echo "Original: " . strlen($bOrig) . " braces, " . substr_count($bOrig, '{') . " {, " . substr_count($bOrig, '}') . " }, net=" . (substr_count($bOrig, '{')-substr_count($bOrig, '}')) . "\n";

// Find first difference
$min = min(strlen($bCurrent), strlen($bOrig));
for ($i = 0; $i < $min; $i++) {
    if ($bCurrent[$i] !== $bOrig[$i]) {
        echo "First difference at structural brace index $i\n";
        echo "Current: ..." . substr($bCurrent, max(0, $i-3), 10) . "...\n";
        echo "Orig:    ..." . substr($bOrig, max(0, $i-3), 10) . "...\n";
        break;
    }
}

// Find where current has an extra brace
echo "\nCurrent brace total net doesn't match, searching for extra {\n";

// Count braces per 50-brace segment
$segSize = 50;
for ($s = 0; $s < strlen($bCurrent); $s += $segSize) {
    $seg = substr($bCurrent, $s, $segSize);
    $net = substr_count($seg, '{') - substr_count($seg, '}');
    if ($net !== 0) {
        echo "Segment starting at brace $s: net=$net, segment=$seg\n";
    }
}
