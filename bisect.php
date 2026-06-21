<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Bisect: Remove methods one by one and test
$methods = [
    'private function splitBySemesterHeader',
    'private function detectDeptFromSubjects', 
    'public function uploadPdf'
];

foreach ($methods as $method) {
    $testCode = $code;
    $start = strpos($testCode, $method);
    if ($start === false) { echo "$method: NOT FOUND\n"; continue; }
    
    // Find the function body { and matching }
    $bracePos = strpos($testCode, '{', $start);
    if ($bracePos === false) { echo "$method: no opening brace\n"; continue; }
    
    // Extract until matching }
    $depth = 0;
    $inSingle = false;
    $inDouble = false;
    $escape = false;
    $endPos = $bracePos;
    for ($i = $bracePos; $i < strlen($testCode); $i++) {
        $ch = $testCode[$i];
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\' && ($inSingle || $inDouble)) { $escape = true; continue; }
        if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
        if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
        if ($inSingle || $inDouble) continue;
        if ($ch === '{') $depth++;
        if ($ch === '}') {
            $depth--;
            if ($depth === 0) { $endPos = $i; break; }
        }
    }
    
    // Remove this method
    $before = substr($testCode, 0, $start);
    $after = substr($testCode, $endPos + 1);
    $testCode = $before . $after;
    
    // Write and test
    file_put_contents(__DIR__ . '/temp_bisect.php', $testCode);
    exec('php -l ' . escapeshellarg(__DIR__ . '/temp_bisect.php') . ' 2>&1', $out, $ret);
    $status = $ret === 0 ? 'OK' : 'FAIL';
    echo "$method removed: $status\n";
    if ($ret !== 0) echo "  " . $out[0] . "\n";
}

echo "\nNow trying removing different inner blocks of uploadPdf...\n";
