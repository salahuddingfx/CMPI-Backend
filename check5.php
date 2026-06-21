<?php
try {
    token_get_all(file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php'));
    echo "OK - No parse errors\n";
} catch (ParseError $e) {
    echo "Parse error: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
}
