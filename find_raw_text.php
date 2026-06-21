<?php
$file = __DIR__ . '/app/Http/Controllers/Api/BtebResultController.php';
$lines = file($file);

// Find lines containing 'raw_text' => that are inside $results[] = [ ... ];
$insideResult = false;
$resultDepth = 0;
$bracketCount = 0;
$rawTextLines = [];

foreach ($lines as $i => $line) {
    $trimmed = trim($line);
    
    // Track $results[] = [ blocks
    if (preg_match('/\$results\[\]\s*=\s*\[/', $line)) {
        $insideResult = true;
        $bracketCount = 1; // the [
        continue;
    }
    
    if ($insideResult) {
        // Count brackets to find when the block ends
        $bracketCount += substr_count($line, '[') - substr_count($line, ']');
        $bracketCount -= substr_count($line, '];'); // ]; is both ] and [
        
        // Check if this line has 'raw_text' =>
        if (str_contains($trimmed, "'raw_text' =>")) {
            $rawTextLines[] = $i;
        }
        
        if ($bracketCount <= 0) {
            $insideResult = false;
        }
    }
}

echo "Found " . count($rawTextLines) . " raw_text lines in \$results[] blocks:\n";
foreach ($rawTextLines as $l) {
    echo "Line " . ($l + 1) . ": " . rtrim($lines[$l]);
}
