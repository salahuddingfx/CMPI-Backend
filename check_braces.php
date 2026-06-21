<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$tokens = @token_get_all($code);
$depth = 0;
$line = 0;
foreach ($tokens as $t) {
    if (is_array($t)) {
        $line = $t[2];
        if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) $depth++;
        continue;
    }
    if ($t === '{') { $depth++; }
    if ($t === '}') { $depth--; }
    if ($depth < 0) { echo "Extra } at line $line\n"; exit; }
}
echo "Final depth: $depth\n";
