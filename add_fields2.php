<?php
$file = __DIR__ . '/app/Http/Controllers/Api/BtebResultController.php';
$lines = file($file);
$output = [];
$inBlock = false;
$depth = 0;
$fieldIndent = '';

for ($i = 0; $i < count($lines); $i++) {
    $line = rtrim($lines[$i], "\n\r");
    $origLine = $line;

    if (!$inBlock) {
        if (preg_match('/\$results\[\]\s*=\s*\[/', $line)) {
            $inBlock = true;
            $depth = substr_count($line, '[') - substr_count($line, ']');
            $fieldIndent = '';
        }
        $output[] = $origLine;
        continue;
    }

    $depth += substr_count($line, '[') - substr_count($line, ']');

    // Track field indentation
    if (preg_match("/^\s*'/", $line)) {
        $spaces = 0;
        while ($spaces < strlen($line) && $line[$spaces] === ' ') $spaces++;
        $fieldIndent = str_repeat(' ', $spaces);
    }

    if ($depth <= 0) {
        // Closing ]; line — insert fields before it
        $indent = $fieldIndent ?: '';
        if ($indent === '') {
            $spaces = 0;
            while ($spaces < strlen($line) && $line[$spaces] === ' ') $spaces++;
            $indent = str_repeat(' ', $spaces);
        }

        $output[] = $indent . "'center_code' => \$centerCode ?? null,";
        $output[] = $indent . "'institute_name' => \$instName ?? null,";
        $output[] = $indent . "'exam_type' => 'regular',";
        $output[] = $origLine;
        $inBlock = false;
    } else {
        $output[] = $origLine;
    }
}

file_put_contents($file, implode("\n", $output));
echo "Done\n";
