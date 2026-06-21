<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Find detectDeptFromSubjects
$start = strpos($code, 'private function detectDeptFromSubjects');
$bracePos = strpos($code, '{', $start);

// Replace the function body with just a return to isolate brace issue
$testCode = substr($code, 0, $start) . 'private function detectDeptFromSubjects(array $subjects, string $defaultDept): string { return $defaultDept; }' . substr($code, $bracePos + 1, 150) . '}';

// Actually let me just replace the entire function body from { to the matching }
$inSingle = false;
$inDouble = false;
$escape = false;
$depth = 0;
$endPos = $bracePos;

for ($i = $bracePos; $i < strlen($code); $i++) {
    $ch = $code[$i];
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

$testCode2 = substr($code, 0, $start) . 'private function detectDeptFromSubjects(array $subjects, string $defaultDept): string { return $defaultDept; }' . substr($code, $endPos + 1);

file_put_contents(__DIR__ . '/temp_skeleton.php', $testCode2);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_skeleton.php') . ' 2>&1', $out, $ret);
echo "Skeleton with detectDeptFromSubjects: " . ($ret === 0 ? "OK" : "FAIL") . "\n";
if ($ret !== 0) { echo implode("\n", $out) . "\n"; }

// Now let me check if the issue is in uploadPdf. Let me replace it with a stub too.
$start2 = strpos($code, 'public function uploadPdf');
$bracePos2 = strpos($code, '{', $start2);

$depth2 = 0;
$inSingle2 = false;
$inDouble2 = false;
$escape2 = false;
$endPos2 = $bracePos2;

for ($i = $bracePos2; $i < strlen($code); $i++) {
    $ch = $code[$i];
    if ($escape2) { $escape2 = false; continue; }
    if ($ch === '\\' && ($inSingle2 || $inDouble2)) { $escape2 = true; continue; }
    if ($ch === "'" && !$inDouble2) { $inSingle2 = !$inSingle2; continue; }
    if ($ch === '"' && !$inSingle2) { $inDouble2 = !$inDouble2; continue; }
    if ($inSingle2 || $inDouble2) continue;
    if ($ch === '{') $depth2++;
    if ($ch === '}') {
        $depth2--;
        if ($depth2 === 0) { $endPos2 = $i; break; }
    }
}

// Replace just the uploadPdf body
$testCode3 = substr($code, 0, $bracePos2) . ' return response()->json(["ok"]); ' . substr($code, $endPos2 + 1);

file_put_contents(__DIR__ . '/temp_skeleton2.php', $testCode3);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_skeleton2.php') . ' 2>&1', $out2, $ret2);
echo "Skeleton with uploadPdf stubbed: " . ($ret2 === 0 ? "OK" : "FAIL") . "\n";
if ($ret2 !== 0) { echo implode("\n", $out2) . "\n"; }
