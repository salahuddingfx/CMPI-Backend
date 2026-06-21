<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
// Find the uploadPdf function body (lines 166-520)
$lines = explode("\n", $code);
$funcLines = array_slice($lines, 165, 355); // Line 166 to 520
$funcCode = implode("\n", $funcLines);

try {
    token_get_all('<?php ' . $funcCode . '?>');
    echo "uploadPdf function: OK\n";
} catch (ParseError $e) {
    echo "uploadPdf parse error: " . $e->getMessage() . "\n";
}

// Test the whole class without uploadPdf
$beforeFunc = implode("\n", array_slice($lines, 0, 165));
$afterFunc = implode("\n", array_slice($lines, 520));
try {
    token_get_all('<?php ' . $beforeFunc . ' function dummy() {} ' . $afterFunc . ' ?>');
    echo "Class without uploadPdf: OK\n";
} catch (ParseError $e) {
    echo "Class without uploadPdf parse error: " . $e->getMessage() . "\n";
}
