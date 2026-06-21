<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
try {
    $tokens = token_get_all($code);
    $depth = 0;
    $line = 0;
    foreach ($tokens as $t) {
        if (is_array($t)) {
            $line = $t[2];
            if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) $depth++;
            continue;
        }
        if ($t === '{') { $depth++; echo "Open at line $line, depth=$depth\n"; }
        if ($t === '}') { 
            $depth--; 
            if ($depth < 0) echo "EXTRA } at line $line\n";
            else echo "Close at line $line, depth=$depth\n";
        }
    }
    echo "Final depth: $depth\n";
} catch (ParseError $e) {
    echo "Parse error: " . $e->getMessage() . " at line " . $e->getLine() . "\n";
}
