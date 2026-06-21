<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Extract just the uploadPdf method body
$start = strpos($code, 'public function uploadPdf');
if ($start === false) { echo "uploadPdf not found\n"; exit; }

// Find the { after the function signature
$bracePos = strpos($code, '{', $start);
if ($bracePos === false) { echo "No opening brace\n"; exit; }

// Extract from the { to the matching } at the same level
$depth = 0;
$inSingle = false;
$inDouble = false;
$escape = false;
$bodyStart = $bracePos;
for ($i = $bracePos; $i < strlen($code); $i++) {
    $ch = $code[$i];
    
    if ($escape) { $escape = false; continue; }
    if ($ch === '\\') { $escape = true; continue; }
    
    if ($ch === "'" && !$inDouble) $inSingle = !$inSingle;
    if ($ch === '"' && !$inSingle) $inDouble = !$inDouble;
    
    if ($inSingle || $inDouble) continue;
    
    if ($ch === '{') $depth++;
    if ($ch === '}') {
        $depth--;
        if ($depth === 0) {
            $bodyEnd = $i;
            break;
        }
    }
}

if (!isset($bodyEnd)) { echo "No matching close brace\n"; exit; }

$funcBody = substr($code, $bodyStart, $bodyEnd - $bodyStart + 1);
$fullFunc = '<?php class X { public function uploadPdf(Request $request) ' . $funcBody . ' }';

// Write to temp file and check
file_put_contents(__DIR__ . '/temp_check.php', $fullFunc);
exec('php -l ' . __DIR__ . '/temp_check.php 2>&1', $output, $ret);
echo implode("\n", $output) . "\n";
echo "Return code: $ret\n";
