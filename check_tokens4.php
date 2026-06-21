<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$tokens = @token_get_all($code);

$opens = 0;
$closes = 0;
$line = 1;
$prevLine = 1;

foreach ($tokens as $t) {
    if (is_array($t)) {
        $line = $t[2];
        if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
            $opens++;
        }
    } else {
        $line = $prevLine;
        if ($t === '{') $opens++;
        if ($t === '}') $closes++;
    }
    $prevLine = $line;
}

echo "Total opens: $opens\n";
echo "Total closes: $closes\n";
echo "Difference: " . ($opens - $closes) . "\n";
