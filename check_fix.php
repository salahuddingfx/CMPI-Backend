<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
// Try adding an extra closing brace at the end
$code2 = $code . "\n}";
file_put_contents(__DIR__ . '/temp_test.php', $code2);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_test.php') . ' 2>&1', $out, $ret);
echo "With extra } at end:\n";
echo implode("\n", $out) . "\n";
echo "Return: $ret\n\n";

// Now try removing the last }
$code3 = substr($code, 0, strrpos($code, '}'));
file_put_contents(__DIR__ . '/temp_test2.php', $code3);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_test2.php') . ' 2>&1', $out2, $ret2);
echo "With last } removed:\n";
echo implode("\n", $out2) . "\n";
echo "Return: $ret2\n";
