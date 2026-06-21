<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Find uploadPdf
$funcPos = strpos($code, 'public function uploadPdf');
$bracePos = strpos($code, '{', $funcPos);

// Extract uploadPdf function body
$depth = 0;
$inSingle = false;
$inDouble = false;
$inHeredoc = false;
$escape = false;
$funcBody = '';

for ($i = $bracePos; $i < strlen($code); $i++) {
    $ch = $code[$i];
    $funcBody .= $ch;
    
    if ($escape) { $escape = false; continue; }
    if ($ch === '\\' && ($inDouble || $inSingle)) { $escape = true; continue; }
    
    if ($ch === "'" && !$inDouble) { $inSingle = !$inSingle; continue; }
    if ($ch === '"' && !$inSingle) { $inDouble = !$inDouble; continue; }
    
    if ($inSingle || $inDouble) continue;
    
    if ($ch === '{') $depth++;
    if ($ch === '}') {
        $depth--;
        if ($depth === 0) {
            // Found the end
            $funcBody = substr($code, $bracePos, $i - $bracePos + 1);
            break;
        }
    }
}

// Create a test file with just this function
$testCode = "<?php\nclass TestController extends Controller {\npublic function uploadPdf(Request \$request) " . $funcBody . "\n}";

file_put_contents(__DIR__ . '/temp_uploadpdf.php', $testCode);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_uploadpdf.php') . ' 2>&1', $out, $ret);
echo "uploadPdf function test:\n";
echo implode("\n", $out) . "\n";
echo "Return: $ret\n";
echo "Function body length: " . strlen($funcBody) . "\n";

// Count braces in func body
$codeBraces = substr_count($funcBody, '{');
$codeCloseBraces = substr_count($funcBody, '}');
echo "In function body: {=$codeBraces, }=$codeCloseBraces\n";
