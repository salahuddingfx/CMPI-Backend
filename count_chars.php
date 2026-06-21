<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Count ALL { and } characters
$totalOpen = substr_count($code, '{');
$totalClose = substr_count($code, '}');
echo "Total '{' characters: $totalOpen\n";
echo "Total '}' characters: $totalClose\n";
echo "Character difference: " . ($totalOpen - $totalClose) . "\n";

// Check line 724 specifically
$lines = explode("\n", $code);
$line724 = $lines[723] ?? 'LINE NOT FOUND';
echo "Line 724: " . bin2hex($line724) . "\n";
echo "Line 724 contains }: " . (strpos($line724, '}') !== false ? 'YES' : 'NO') . "\n";
echo "Line 724 content: ";
var_dump($line724);
