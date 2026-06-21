<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$tokens = @token_get_all($code);

$depth = 0;
$line = 1;
$prevLine = 1;
$checkLines = range(485, 530);

foreach ($tokens as $t) {
    if (is_array($t)) {
        $line = $t[2];
        if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
            $depth++;
            if (in_array($line, $checkLines)) echo "Line $line: T_CURLY_OPEN depth=$depth\n";
        }
    } else {
        $line = $prevLine;
        if ($t === '{') {
            $depth++;
            if (in_array($line, $checkLines)) echo "Line $line: { depth=$depth\n";
        }
        if ($t === '}') {
            $depth--;
            if (in_array($line, $checkLines)) echo "Line $line: } depth=$depth\n";
            if ($depth < 0) echo "Line $line: EXTRA } depth=$depth\n";
        }
    }
    $prevLine = $line;
}
echo "Final depth: $depth\n";
