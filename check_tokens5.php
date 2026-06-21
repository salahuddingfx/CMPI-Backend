<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$tokens = @token_get_all($code);

$depth = 0;
$line = 1;
$prevLine = 1;
$stack = [];

foreach ($tokens as $t) {
    if (is_array($t)) {
        $line = $t[2];
        if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
            $depth++;
            $stack[] = ['line' => $line, 'type' => 'string_interp'];
        }
    } else {
        $line = $prevLine;
        if ($t === '{') {
            $depth++;
            $stack[] = ['line' => $line, 'type' => 'code_block'];
        }
        if ($t === '}') {
            $depth--;
            if (count($stack) > 0) {
                array_pop($stack);
            } else {
                echo "Extra } at line $line\n";
            }
        }
    }
    $prevLine = $line;
}

echo "\nUnmatched opens:\n";
foreach ($stack as $s) {
    echo "Line {$s['line']}: {$s['type']}\n";
}
echo "Final depth: $depth\n";
