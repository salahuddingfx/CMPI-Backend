<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Use PHP tokenizer to find all code { and } positions
$tokens = @token_get_all($code);

$depth = 0;
$line = 1;
$prevLine = 1;
$tokenPos = 0;

$maxDepth = 0;
$depthZeroPositions = [];

foreach ($tokens as $idx => $t) {
    if (is_array($t)) {
        $line = $t[2];
        if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
            $depth++;
        }
    } else {
        $line = $prevLine;
        if ($t === '{') {
            $depth++;
        }
        if ($t === '}') {
            $depth--;
            if ($depth === 0) {
                $depthZeroPositions[] = ['token_idx' => $idx, 'line' => $line, 'char' => $t];
            }
        }
    }
    $prevLine = $line;
}

echo "Depth at end: $depth\n";
echo "Number of times depth hits 0 (class level): " . count($depthZeroPositions) . "\n";
echo "Lines where depth=0:\n";
foreach ($depthZeroPositions as $p) {
    echo "  Token {$p['token_idx']} at line {$p['line']}\n";
}
