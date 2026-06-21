<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$tokens = @token_get_all($code);

$line = 1;
$prevLine = 1;
$allBraces = [];

foreach ($tokens as $t) {
    if (is_array($t)) {
        $line = $t[2];
        if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
            $allBraces[] = ['line' => $line, 'type' => 'OPEN_SPECIAL'];
        }
    } else {
        $line = $prevLine;
        if ($t === '{') {
            $allBraces[] = ['line' => $line, 'type' => 'OPEN'];
        }
        if ($t === '}') {
            $allBraces[] = ['line' => $line, 'type' => 'CLOSE'];
        }
    }
    $prevLine = $line;
}

// Show all close/open around line 520
foreach ($allBraces as $i => $b) {
    if ($b['line'] >= 515 && $b['line'] <= 525) {
        echo "Index $i: Line {$b['line']} {$b['type']}\n";
    }
}

// Show last 20 braces
echo "\nLast 20 braces:\n";
$last20 = array_slice($allBraces, -20);
foreach ($last20 as $b) {
    echo "Line {$b['line']} {$b['type']}\n";
}
