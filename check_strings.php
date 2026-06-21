<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);

// Show lines with double-quoted strings
foreach ($lines as $i => $line) {
    if (preg_match('/"[^"]*"/', $line) || preg_match("/'[^']*'/", $line)) {
        // Check for interpolation
        if (preg_match('/\$[a-zA-Z_]/', $line)) {
            echo "Line " . ($i+1) . ": $line\n";
        }
    }
}

echo "\n--- Check for unclosed strings ---\n";
// Count quotes
$inString = false;
$strChar = '';
$escape = false;
$lineNum = 1;
$code2 = $code;
$len = strlen($code2);

// First, remove all comments
$code2 = preg_replace('/\/\/.*$/m', '', $code2);
$code2 = preg_replace('/#.*$/m', '', $code2);
// Remove block comments - might be multiline, need care
$code2 = preg_replace('/\/\*.*?\*\//s', '', $code2);
$lines2 = explode("\n", $code2);
$potentialIssues = [];

for ($p = 0; $p < strlen($code2); $p++) {
    $ch = $code2[$p];
    if ($ch === "\n") { $lineNum++; continue; }
    
    if ($escape) { $escape = false; continue; }
    if ($ch === '\\') { $escape = true; continue; }
    
    if ($inString) {
        if ($ch === $strChar) {
            // Check for heredoc end
            $inString = false;
        }
        continue;
    }
    
    if ($ch === '"' || $ch === "'") {
        // Check if it's a heredoc start
        $inString = true;
        $strChar = $ch;
    }
}

if ($inString) {
    echo "WARNING: Unclosed string starting with $strChar at approximately line $lineNum\n";
} else {
    echo "All strings seem properly closed\n";
}
