<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);

// Start from uploadPdf function
$depth = 0;
$inSingle = false;
$inDouble = false;
$recording = false;
$startLine = 0;

foreach ($lines as $i => $line) {
    $num = $i + 1;
    
    if (strpos($line, 'public function uploadPdf') !== false) {
        $recording = true;
        $startLine = $num;
    }
    
    if (!$recording) continue;
    
    // Actually let's just count braces per line more carefully
}

// Brute force: remove strings and comments, then count
$clean = '';
$prevLine = 0;
foreach (token_get_all($code) as $t) {
    if (is_array($t)) {
        if ($t[0] === T_CONSTANT_ENCAPSED_STRING || 
            $t[0] === T_ENCAPSED_AND_WHITESPACE ||
            $t[0] === T_WHITESPACE ||
            $t[0] === T_COMMENT ||
            $t[0] === T_DOC_COMMENT) {
            continue;
        }
        if ($t[0] === T_OPEN_TAG || $t[0] === T_OPEN_TAG_WITH_ECHO) continue;
        $clean .= $t[1];
    } else {
        $clean .= $t;
    }
}

// Now count braces
$lines2 = explode("\n", $clean);
$inFunc = false;
$depth = 0;
$funcStart = 0;
$openBraceLine = 0;
$actualLine = 0;

// Re-map line numbers by counting newlines in the original code up to each token position
// This is complex, let me take a different approach

// Just find uploadPdf and count from there
echo "File line-by-line brace count (excluding strings):\n";
$depth = 0;
$inString = false;
$strChar = '';
$escape = false;
$funcDepth = 0;
$inFunc2 = false;

for ($i = 0; $i < strlen($code); $i++) {
    $ch = $code[$i];
    
    if ($ch === "\n") continue; // count at token level, not line level
    
    if ($escape) { $escape = false; continue; }
    if ($ch === '\\' && ($inString)) { $escape = true; continue; }
    
    if (($ch === '"' || $ch === "'") && !$inString) {
        $inString = true;
        $strChar = $ch;
        continue;
    }
    if ($ch === $strChar && $inString) {
        $inString = false;
        $strChar = '';
        continue;
    }
    
    if ($inString) continue;
    
    if ($ch === '{') $depth++;
    if ($ch === '}') $depth--;
}

echo "Overall depth from tokens (excl strings): $depth\n";
