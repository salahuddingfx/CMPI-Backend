<?php
function strip_strings($code) {
    $result = '';
    $tokens = token_get_all($code);
    foreach ($tokens as $t) {
        if (is_array($t)) {
            if ($t[0] === T_CONSTANT_ENCAPSED_STRING || 
                $t[0] === T_ENCAPSED_AND_WHITESPACE ||
                $t[0] === T_STRING ||
                $t[0] === T_LNUMBER ||
                $t[0] === T_DNUMBER ||
                $t[0] === T_COMMENT ||
                $t[0] === T_DOC_COMMENT ||
                $t[0] === T_WHITESPACE) {
                continue;
            }
            $result .= $t[1];
        } else {
            $result .= $t;
        }
    }
    return $result;
}

$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');

// Remove all strings, comments, whitespace
$stripped = '';
$tokens = token_get_all($code);
foreach ($tokens as $t) {
    if (is_array($t)) {
        if ($t[0] === T_CONSTANT_ENCAPSED_STRING || 
            $t[0] === T_ENCAPSED_AND_WHITESPACE ||
            $t[0] === T_WHITESPACE ||
            $t[0] === T_COMMENT ||
            $t[0] === T_DOC_COMMENT) {
            continue;
        }
        $stripped .= $t[1];
    } else {
        $stripped .= $t;
    }
}

$depth = 0;
$line = 1;
for ($i = 0; $i < strlen($stripped); $i++) {
    $ch = $stripped[$i];
    if ($ch === "\n") { $line++; continue; }
    if ($ch === '{') {
        $depth++;
        echo "Open at line ~$line depth=$depth\n";
    }
    if ($ch === '}') {
        $depth--;
        echo "Close at line ~$line depth=$depth\n";
        if ($depth < 0) {
            echo "  *** EXTRA } at line ~$line ***\n";
        }
    }
}
echo "Final depth: $depth\n";
