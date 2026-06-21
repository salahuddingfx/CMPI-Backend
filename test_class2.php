<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);

// Test chunk lines 522-724 (methods after uploadPdf)
$chunk = '<?php
class Test {
' . implode("\n", array_slice($lines, 520)) . '
}';
file_put_contents(__DIR__ . '/temp_chunk.php', $chunk);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk.php') . ' 2>&1', $out, $ret);
echo "LINES 522-724: " . ($ret === 0 ? "OK" : "FAIL") . "\n";
if ($ret !== 0) { echo implode("\n", $out) . "\n"; }

// Test chunk lines 166-520 (uploadPdf function)
$chunk2 = '<?php
class Test2 {
' . implode("\n", array_slice($lines, 165, 356)) . '
}';
file_put_contents(__DIR__ . '/temp_chunk2.php', $chunk2);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk2.php') . ' 2>&1', $out2, $ret2);
echo "LINES 166-521: " . ($ret2 === 0 ? "OK" : "FAIL") . "\n";
if ($ret2 !== 0) { echo implode("\n", $out2) . "\n"; }

// Now individually test each internal method
echo "\n--- Individual methods ---\n";

// Test splitBySemesterHeader only
$chunk3 = '<?php
class Test3 {
' . implode("\n", array_slice($lines, 521, 50)) . '
}';
file_put_contents(__DIR__ . '/temp_chunk3.php', $chunk3);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk3.php') . ' 2>&1', $out3, $ret3);
echo "splitBySemesterHeader: " . ($ret3 === 0 ? "OK" : "FAIL") . "\n";
if ($ret3 !== 0) { echo implode("\n", $out3) . "\n"; }

// Test detectDeptFromSubjects only
$chunk4 = '<?php
class Test4 {
' . implode("\n", array_slice($lines, 565)) . '
}';
file_put_contents(__DIR__ . '/temp_chunk4.php', $chunk4);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk4.php') . ' 2>&1', $out4, $ret4);
echo "detectDeptFromSubjects: " . ($ret4 === 0 ? "OK" : "FAIL") . "\n";
if ($ret4 !== 0) { echo implode("\n", $out4) . "\n"; }
