<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Find the class definition and test content sections
// Try commenting out the class and see if remaining methods have syntax error
$lines = explode("\n", $code);

// Method 1: Test if maybe the class close + class open work
// Let's test all methods after uploadPdf separately
$split1 = implode("\n", array_slice($lines, 0, 165)); // Before uploadPdf
$split2 = implode("\n", array_slice($lines, 165, 356)); // uploadPdf (lines 166-521)
$split3 = implode("\n", array_slice($lines, 521)); // After uploadPdf (lines 522+)

echo "Part 1 (before uploadPdf) length: " . strlen($split1) . "\n";

// Test uploadPdf part
$test1 = "<?php\nnamespace App\Http\Controllers\Api;\nuse App\Models\BtebResult;\nuse App\Models\ImportJob;\nuse App\Jobs\ProcessBtebDriveImport;\nuse Smalot\PdfParser\Parser;\nclass Test {\n" . implode("\n", array_slice($lines, 165, 356)) . "\n}";
file_put_contents(__DIR__ . '/test_part2.php', $test1);
exec('php -l ' . escapeshellarg(__DIR__ . '/test_part2.php') . ' 2>&1', $out, $ret);
echo "Part 2 (uploadPdf alone) test: " . ($ret === 0 ? "OK" : "FAIL") . "\n";
if ($ret !== 0) echo "  " . $out[0] . "\n";

// Test splitBySemesterHeader + detectDeptFromSubjects
$test2 = "<?php\nnamespace App\Http\Controllers\Api;\nuse App\Models\BtebResult;\nuse Smalot\PdfParser\Parser;\nclass Test {\n" . implode("\n", array_slice($lines, 521)) . "\n}";
file_put_contents(__DIR__ . '/test_part3.php', $test2);
exec('php -l ' . escapeshellarg(__DIR__ . '/test_part3.php') . ' 2>&1', $out, $ret);
echo "Part 3 (after uploadPdf) test: " . ($ret === 0 ? "OK" : "FAIL") . "\n";
if ($ret !== 0) echo "  " . $out[0] . "\n";
