<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
ini_set('xdebug.max_nesting_level', 10000);
try {
    $tokens = token_get_all($code);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit;
}

$last10 = array_slice($tokens, -10);
foreach ($last10 as $t) {
    if (is_array($t)) {
        echo "Line {$t[2]}: " . token_name($t[0]) . " = " . json_encode($t[1]) . "\n";
    } else {
        echo "Literal: $t\n";
    }
}
