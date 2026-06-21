<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Count actual code braces (not in strings) using a real PHP approach
// Let's actually try to fix this by bisecting

// Test: remove the detectDeptFromSubjects method and its braces
$start = strpos($code, 'private function detectDeptFromSubjects');
if ($start) {
    $endPos = strrpos($code, '}');
    $braceEnd = strrpos($code, '}', $endPos - 1);
    $code2 = substr($code, 0, $start) . '// detectDeptFromSubjects removed' . substr($code, $braceEnd);
    // But we need to match the right brace
}

// Let's instead try: the problem is in uploadPdf. Let's count how many brace chars are in strings
// by looking at single and double quoted strings
$inSingle = false;
$inDouble = false;
$escape = false;
$codeBraces = 0;
$stringBraces = 0;
$stringCloseBraces = 0;
$codeCloseBraces = 0;
$lineNum = 1;

for ($i = 0; $i < strlen($code); $i++) {
    $ch = $code[$i];
    if ($ch === "\n") { $lineNum++; continue; }
    
    if ($escape) { $escape = false; continue; }
    if ($ch === '\\') { $escape = true; continue; }
    
    if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
    if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
    
    if ($inSingle || $inDouble) {
        if ($ch === '{') $stringBraces++;
        if ($ch === '}') $stringCloseBraces++;
        continue;
    }
    
    if ($ch === '{') $codeBraces++;
    if ($ch === '}') $codeCloseBraces++;
}

echo "In strings: {=" . ($stringBraces) . ", }=" . ($stringCloseBraces) . "\n";
echo "In code:   {=" . ($codeBraces) . ", }=" . ($codeCloseBraces) . "\n";
echo "Code diff: " . ($codeBraces - $codeCloseBraces) . "\n";

// Now list all code {} positions
echo "\nCode { and } positions (non-string):\n";
$inSingle = false;
$inDouble = false;
$escape = false;
$pos = 0;
$braces = [];

for ($i = 0; $i < strlen($code); $i++) {
    $ch = $code[$i];
    if ($escape) { $escape = false; continue; }
    if ($ch === '\\') { $escape = true; continue; }
    
    if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
    if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
    
    if ($inSingle || $inDouble) continue;
    
    if ($ch === '{') $braces[] = ['char' => '{', 'pos' => $i];
    if ($ch === '}') $braces[] = ['char' => '}', 'pos' => $i];
}

// Test balance with simple stack
$stack = [];
$extraClose = [];
$unmatchedOpen = [];

foreach ($braces as $b) {
    if ($b['char'] === '{') {
        $stack[] = $b;
    } else {
        if (count($stack) === 0) {
            $extraClose[] = $b;
        } else {
            array_pop($stack);
        }
    }
}

$unmatchedOpen = $stack;

echo "\nExtra closes: " . count($extraClose) . "\n";
foreach ($extraClose as $b) {
    // Find line number
    $line = substr_count(substr($code, 0, $b['pos']), "\n") + 1;
    echo "  Extra } at position {$b['pos']}, approx line $line\n";
}

echo "\nUnmatched opens: " . count($unmatchedOpen) . "\n";
foreach ($unmatchedOpen as $b) {
    $line = substr_count(substr($code, 0, $b['pos']), "\n") + 1;
    echo "  Unmatched { at position {$b['pos']}, approx line $line\n";
}
