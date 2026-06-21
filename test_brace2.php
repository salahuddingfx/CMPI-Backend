<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);

// Tokenize the whole file
$tokens = token_get_all($code);
$opens = 0;
$closes = 0;
$details = [];

foreach ($tokens as $idx => $token) {
    if (is_array($token)) {
        $name = token_name($token[0]);
        $text = $token[1];
        $line = $token[2];
        if ($name === 'T_CURLY_OPEN' || ($token[0] === 75)) { // T_CURLY_OPEN or curly brace
            if ($name === 'T_CURLY_OPEN' || $text === '{' || $text === '}') {
                $type = $text === '{' ? 'OPEN' : ($text === '}' ? 'CLOSE' : 'UNKNOWN');
                if ($type === 'OPEN') {
                    $opens++;
                    if ($opens > 79) echo "Extra open #$opens at line $line: text=$text token=$name\n";
                }
                if ($type === 'CLOSE') {
                    $closes++;
                }
                $details[] = "Line $line: $type ($name) text=" . substr(json_encode($text), 0, 40);
            }
        }
    } else {
        if ($token === '{') {
            $opens++;
            if ($opens > 79) echo "Extra open #$opens at line ?: text={\n";
            $details[] = "Line ?: OPEN (simple char) text={";
        }
        if ($token === '}') {
            $closes++;
            $details[] = "Line ?: CLOSE (simple char) text=}";
        }
    }
}

echo "\nTotal: opens=$opens closes=$closes diff=" . ($opens - $closes) . "\n\n";

// Find where opens > 79
$o = 0;
$c = 0;
foreach ($tokens as $idx => $token) {
    $text = is_array($token) ? $token[1] : $token;
    $line = is_array($token) ? $token[2] : '?';
    
    if ($text === '{' || (is_array($token) && $token[0] === 272)) { // T_CURLY_OPEN
        $o++;
        if ($o > 79) echo "Opens exceed 79 at token $idx, line $line, text=$text (token " . (is_array($token) ? token_name($token[0]) : 'char') . ")\n";
    }
    if ($text === '}' && $o >= $c) {
        $c++;
    }
}
