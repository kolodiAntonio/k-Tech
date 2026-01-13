<?php
// error-tail.php - temporary helper to print last lines of server error_log
// IMPORTANT: remove this file after testing
header('Content-Type: text/plain; charset=utf-8');
echo "error-tail.php - last lines of server error_log\n\n";

$cwd = getcwd();
$paths = [];
// look for common error_log locations starting from current dir and up
$dir = $cwd;
for ($i = 0; $i < 6; $i++) {
    $paths[] = $dir . DIRECTORY_SEPARATOR . 'error_log';
    $paths[] = $dir . DIRECTORY_SEPARATOR . 'error-log';
    $parent = dirname($dir);
    if ($parent === $dir) break;
    $dir = $parent;
}
// include ini_get('error_log') if set
$iniErrorLog = ini_get('error_log');
if ($iniErrorLog) $paths[] = $iniErrorLog;
// unique and filter
$paths = array_values(array_unique($paths));
$found = null;
foreach ($paths as $p) {
    if (file_exists($p) && is_readable($p)) { $found = $p; break; }
}
if (!$found) {
    echo "No readable error_log found in common locations.\n";
    echo "Searched paths:\n" . implode("\n", $paths) . "\n\n";
    echo "PHP error_log (ini): " . ($iniErrorLog ?: '(none)') . "\n";
    echo "If you use Hostinger/hPanel, open Logs → Error Logs or place .env in public_html and try env-diag.php.\n";
    echo "\nRemove this file after use.\n";
    exit;
}

echo "Found error_log: $found\n\n";
// Read last ~200 lines efficiently
$lines = @file($found, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    echo "Unable to read file contents.\n";
    exit;
}
$tail = array_slice($lines, -200);
foreach ($tail as $ln) echo $ln . "\n";

echo "\n-- END --\n";

echo "\nIMPORTANT: remove this file from the server after testing.\n";
?>