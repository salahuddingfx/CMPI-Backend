<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
echo "File length: " . strlen($code) . "\n";
echo "Last 10 bytes hex: " . bin2hex(substr($code, -10)) . "\n";
echo "Last 10 bytes: " . json_encode(substr($code, -10)) . "\n";

// Check if last char is }
echo "Last char: " . json_encode(substr($code, -1)) . " hex: " . bin2hex(substr($code, -1)) . "\n";

$lines = explode("\n", $code);
echo "Total lines: " . count($lines) . "\n";
$lastLine = end($lines);
echo "Last line content: " . json_encode($lastLine) . "\n";
echo "Last line hex: " . bin2hex($lastLine) . "\n";

// Check if file ends with \n or not
echo "File ends with \\n: " . (substr($code, -1) === "\n" ? "YES" : "NO") . "\n";
echo "File ends with }: " . (substr($code, -1) === '}' ? "YES" : "NO") . "\n";
