<?php
$output = [];
$ret = 0;
exec('php -l ' . escapeshellarg(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php') . ' 2>&1', $output, $ret);
echo implode("\n", $output) . "\n";
echo "Return code: $ret\n";
