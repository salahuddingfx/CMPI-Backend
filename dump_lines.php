<?php
$f = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $f);
for ($i = 515; $i <= 525; $i++) {
    echo 'Line ' . ($i + 1) . ': ';
    var_dump($lines[$i]);
}
