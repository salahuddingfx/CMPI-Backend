<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
try {
    $tokens = token_get_all($code);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit;
}

// Show all } tokens
echo "All close braces with line numbers:\n";
foreach ($tokens as $i => $t) {
    if (!is_array($t) && $t === '}') {
        // Find the line number from nearby tokens
        $line = '?';
        for ($j = $i; $j >= 0 && $j > $i - 5; $j--) {
            if (is_array($tokens[$j])) {
                $line = $tokens[$j][2];
                break;
            }
        }
        echo "Index $i: Close brace at approx line $line\n";
    }
}

// Show last 5 {} pairs in full context
echo "\n\nLast 15 tokens:\n";
$last15 = array_slice($tokens, -15);
foreach ($last15 as $t) {
    if (is_array($t)) {
        echo "Line {$t[2]}: " . token_name($t[0]) . " = " . json_encode($t[1]) . "\n";
    } else {
        echo "Literal: $t\n";
    }
}

echo "\n\nTotal tokens: " . count($tokens) . "\n";

// Parse out the actual text of the last ~100 bytes
$text = substr($code, -100);
echo "\nLast 100 bytes:\n$text\n";
echo "Hex: " . bin2hex($text) . "\n";
