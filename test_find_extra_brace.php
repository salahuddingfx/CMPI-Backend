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

function mapBraceToFile($code, $braceIndex) {
    $braces = '';
    $len = strlen($code);
    $inSingle = false;
    $inDouble = false;
    $escape = false;
    $bracePos = 0;
    for ($i = 0; $i < $len; $i++) {
        $ch = $code[$i];
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; continue; }
        if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
        if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
        if ($inSingle || $inDouble) continue;
        if ($ch === '{' || $ch === '}') {
            if ($bracePos === $braceIndex) {
                // Count line number up to $i
                $lineNum = 1;
                for ($j = 0; $j < $i; $j++) {
                    if ($code[$j] === "\n") $lineNum++;
                }
                // Find the line content
                $lineStart = strrpos(substr($code, 0, $i), "\n");
                if ($lineStart === false) $lineStart = 0; else $lineStart++;
                $lineEnd = strpos($code, "\n", $i);
                if ($lineEnd === false) $lineEnd = strlen($code);
                $lineContent = substr($code, $lineStart, $lineEnd - $lineStart);
                return "Brace $braceIndex: '$ch' at file position $i, line $lineNum: $lineContent";
            }
            $bracePos++;
        }
    }
    return "Not found";
}

$current = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Find which brace index is the problem
$bCurrent = getStructuralBraces($current);

// Walk through and track cumulative depth per brace index
$depth = 0;
$lastOpenIndex = -1;
$lastOpenDepth = 0;
for ($i = 0; $i < strlen($bCurrent); $i++) {
    if ($bCurrent[$i] === '{') {
        $depth++;
        $lastOpenIndex = $i;
        $lastOpenDepth = $depth;
    }
    if ($bCurrent[$i] === '}') {
        $depth--;
    }
    if ($depth < 0) {
        echo "Unmatched } at structural brace index $i (depth=$depth)\n";
        echo mapBraceToFile($current, $i) . "\n";
        break;
    }
}
echo "Total braces: " . strlen($bCurrent) . ", {=" . substr_count($bCurrent, '{') . ", }=" . substr_count($bCurrent, '}') . ", net=" . (substr_count($bCurrent, '{') - substr_count($bCurrent, '}')) . "\n";
echo "Final depth after processing all braces: $depth\n";
echo "Last open brace was at index $lastOpenIndex with depth=$lastOpenDepth\n";
if ($lastOpenIndex >= 0) {
    echo "Last open brace: " . mapBraceToFile($current, $lastOpenIndex) . "\n";
}

if ($depth > 0) {
    echo "File ends with depth=$depth, missing closing braces\n";
    // Find all opens that never close
    $depth = 0;
    $opens = [];
    for ($i = 0; $i < strlen($bCurrent); $i++) {
        if ($bCurrent[$i] === '{') {
            $depth++;
            $opens[$depth] = $i;
        }
        if ($bCurrent[$i] === '}') {
            unset($opens[$depth]); // remove the matched open
            $depth--;
        }
    }
    // Remaining opens are unmatched
    foreach ($opens as $d => $idx) {
        echo "Unmatched { at depth $d, brace index $idx: " . mapBraceToFile($current, $idx) . "\n";
    }
} elseif ($depth === 0) {
    echo "All braces balanced!\n";
}
