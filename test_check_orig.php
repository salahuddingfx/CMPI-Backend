<?php
// Extract original from git and test its syntax
$origGit = shell_exec('git -C ' . escapeshellarg(__DIR__) . ' show HEAD:app/Http/Controllers/Api/BtebResultController.php 2>&1');
file_put_contents(__DIR__ . '/test_orig_from_git.php', $origGit);
exec('php -l ' . escapeshellarg(__DIR__ . '/test_orig_from_git.php') . ' 2>&1', $out, $ret);
echo "Original from git: " . ($ret === 0 ? "OK" : "FAIL") . "\n";
if ($ret !== 0) echo implode("\n", $out) . "\n";
