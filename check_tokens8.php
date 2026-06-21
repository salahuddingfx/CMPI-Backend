<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
ini_set('xdebug.max_nesting_level', 10000);
try {
    $tokens = token_get_all($code);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit;
}

// Check if the last token is the final }
$last = $tokens[count($tokens) - 1];
if (is_array($last)) {
    echo "Last token: " . token_name($last[0]) . " at line {$last[2]}: " . json_encode($last[1]) . "\n";
} else {
    echo "Last token: literal '$last'\n";
}

// Show the last few characters from the file
$codeEnd = substr($code, -30);
echo "Last 30 chars of file: " . json_encode($codeEnd) . "\n";

// Check the token around position 36700
$total = count($tokens);
echo "\nTotal tokens: $total\n";
echo "Last 5 tokens:\n";
for ($i = $total - 5; $i < $total; $i++) {
    $t = $tokens[$i];
    if (is_array($t)) {
        echo "  [{$t[2]}] " . token_name($t[0]) . " = " . json_encode($t[1]) . "\n";
    } else {
        echo "  literal: " . json_encode($t) . "\n";
    }
}

// Count ALL open and close braces including string interpolation
$opens = 0;
$closes = 0;
foreach ($tokens as $t) {
    if (is_array($t)) {
        if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
            $opens++;
        }
    } else {
        if ($t === '{') $opens++;
        if ($t === '}') $closes++;
    }
}
echo "\nToken counts: {=$opens, }=$closes\n";
