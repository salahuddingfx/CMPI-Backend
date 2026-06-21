<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$tokens = token_get_all($code);

$depth = 0;
$line = 1;
$prevLine = 1;

foreach ($tokens as $t) {
    if (is_array($t)) {
        $line = $t[2];
        // Only count actual code-block braces, not from T_CURLY_OPEN (e.g., "{$var}")
        if ($t[0] === T_CURLY_OPEN || $t[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
            $depth++; 
        }
    } else {
        $line = $prevLine;
        if ($t === '{') {
            $depth++;
            if ($depth === 1) echo "Class open at line $line\n";
        }
        if ($t === '}') {
            $depth--;
            if ($depth === 0) echo "Class close at line $line\n";
            if ($depth < 0) echo "Extra } at line $line!\n";
        }
    }
    $prevLine = $line;
}
echo "Final depth: $depth\n";
