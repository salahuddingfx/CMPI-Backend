<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);

// Test detectDeptFromSubjects properly (line 572-724)
$chunk4 = '<?php
class Test4 {
' . implode("\n", array_slice($lines, 571)) . '
}';
file_put_contents(__DIR__ . '/temp_chunk4b.php', $chunk4);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk4b.php') . ' 2>&1', $out4, $ret4);
echo "detectDeptFromSubjects: " . ($ret4 === 0 ? "OK" : "FAIL") . "\n";
if ($ret4 !== 0) { echo implode("\n", $out4) . "\n"; }

// Test the full file without detectDeptFromSubjects
$before = implode("\n", array_slice($lines, 0, 571));
$after = implode("\n", array_slice($lines, 724)); // after detectDeptFromSubjects
$test5 = $before . $after;
file_put_contents(__DIR__ . '/temp_chunk5.php', $test5);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk5.php') . ' 2>&1', $out5, $ret5);
echo "Without detectDeptFromSubjects: " . ($ret5 === 0 ? "OK" : "FAIL") . "\n";
if ($ret5 !== 0) { echo implode("\n", $out5) . "\n"; }

// Test uploadPdf alone more carefully
$funcStart = strpos($code, 'public function uploadPdf');
$linesBeforeFunc = explode("\n", substr($code, 0, $funcStart));
$funcStartLine = count($linesBeforeFunc); // line number where function starts

$chunk6 = '<?php
class Test6 {
' . implode("\n", array_slice($lines, $funcStartLine - 1, 356)) . '
}';
file_put_contents(__DIR__ . '/temp_chunk6.php', $chunk6);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk6.php') . ' 2>&1', $out6, $ret6);
echo "uploadPdf: " . ($ret6 === 0 ? "OK" : "FAIL") . "\n";
if ($ret6 !== 0) { echo implode("\n", $out6) . "\n"; }

// Test the whole class with ALL methods (full file) but wrap in fresh class
$allContent = implode("\n", array_slice($lines, 16, -1)); // skip <?php, namespace, use, class { and final }
$chunk7 = '<?php
class BtebResultController extends Controller
{
' . implode("\n", array_slice($lines, 16, 708)) . '
}';
file_put_contents(__DIR__ . '/temp_chunk7.php', $chunk7);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk7.php') . ' 2>&1', $out7, $ret7);
echo "All methods: " . ($ret7 === 0 ? "OK" : "FAIL") . "\n";
if ($ret7 !== 0) { echo implode("\n", $out7) . "\n"; }
