<?php
$file = __DIR__ . '/app/Http/Controllers/Api/BtebResultController.php';
$code = file_get_contents($file);

// Add center_code, institute_name, exam_type to each $results[] = [...] block
// Pattern: match $results[] = [  ...  'raw_text' => ...,  ];  and insert fields

// Find all $results[] = [ blocks and add the fields
$resultPattern = '/(\$results\[\] = \[)([^;]+?)(\];)/s';

$code = preg_replace_callback($resultPattern, function ($m) {
    $body = $m[2];
    
    // Check if already has these fields
    if (str_contains($body, "'exam_type'")) {
        return $m[0]; // already has it
    }
    
    // Determine indentation from the closing ];
    $lines = explode("\n", $m[3]);
    $closeLine = $lines[0]; // e.g., "                    ];"
    $indent = '';
    for ($i = 0; $i < strlen($closeLine); $i++) {
        if ($closeLine[$i] === ' ') $indent .= ' ';
        else break;
    }
    
    // Add fields before the closing ];
    $newFields = "\n";
    $newFields .= $indent . "'center_code' => \$centerCode ?? null,\n";
    $newFields .= $indent . "'institute_name' => \$instName ?? null,\n";
    $newFields .= $indent . "'exam_type' => 'regular',\n";
    
    return $m[1] . $body . $newFields . $indent . '];';
}, $code);

file_put_contents($file, $code);
echo "Done\n";
