<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$tokens = @token_get_all($code);
$opens = 0;
$closes = 0;
foreach ($tokens as $t) {
    if (is_array($t)) {
        if ($t[0] === 272) { // T_CURLY_OPEN
            $opens++;
            echo "T_CURLY_OPEN at line {$t[2]}: " . json_encode($t[1]) . "\n";
        }
        if ($t[0] === 275) { // T_DOLLAR_OPEN_CURLY_BRACES  
            $opens++;
            echo "T_DOLLAR_OPEN_CURLY_BRACES at line {$t[2]}: " . json_encode($t[1]) . "\n";
        }
    } else {
        if ($t === '{') $opens++;
        if ($t === '}') $closes++;
    }
}
echo "Opens: $opens, Closes: $closes, Diff: " . ($opens - $closes) . "\n";
