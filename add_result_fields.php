<?php
$file = __DIR__ . '/app/Http/Controllers/Api/BtebResultController.php';
$code = file_get_contents($file);

$lines = explode("\n", $code);
$output = [];
$inResultBlock = false;
$lastFieldIndent = '';

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    
    if (!$inResultBlock) {
        if (preg_match('/\$results\[\]\s*=\s*\[/', $line)) {
            $inResultBlock = true;
            $depth = substr_count($line, '[') - substr_count($line, ']');
        }
        $output[] = $line;
        continue;
    }
    
    // Inside a $results[] = [ block
    $depth += substr_count($line, '[') - substr_count($line, ']');
    
    // Track field indentation (lines with 'something' =>)
    if (preg_match("/^\s*'/", $line)) {
        $indent = '';
        for ($j = 0; $j < strlen($line); $j++) {
            if ($line[$j] === ' ') $indent .= ' ';
            else break;
        }
        $lastFieldIndent = $indent;
    }
    
    if ($depth <= 0) {
        // Use the field indent (or fallback to ] line indent)
        $indent = $lastFieldIndent;
        if (empty($indent)) {
            for ($j = 0; $j < strlen($line); $j++) {
                if ($line[$j] === ' ') $indent .= ' ';
                else break;
            }
        }
        
        $output[] = $indent . "'center_code' => \$centerCode ?? null,";
        $output[] = $indent . "'institute_name' => \$instName ?? null,";
        $output[] = $indent . "'exam_type' => 'regular',";
        $output[] = $line;
        $inResultBlock = false;
    } else {
        $output[] = $line;
    }
}

file_put_contents($file, implode("\n", $output));
echo "Done\n";
