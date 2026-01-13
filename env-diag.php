<?php
// Temporary diagnostic — safe to run publicly. Does NOT print .env contents.
header('Content-Type: text/plain; charset=utf-8');

echo "env-diag diagnostic\n";
echo "===================\n\n";

echo "script_dir: " . __DIR__ . PHP_EOL;
echo "cwd: " . getcwd() . PHP_EOL;
echo "PHP SAPI: " . php_sapi_name() . PHP_EOL;

if (function_exists('posix_geteuid')) {
    echo "uid/gid: " . posix_geteuid() . '/' . posix_getegid() . PHP_EOL;
} else {
    echo "uid/gid: n/a (posix functions unavailable)" . PHP_EOL;
}

echo PHP_EOL;

$start = __DIR__;
$paths = [];
$search = $start;
for ($i = 0; $i < 8; $i++) {
    $paths[] = $search . '/.env';
    $parent = dirname($search);
    if ($parent === $search) break;
    $search = $parent;
}

foreach ($paths as $p) {
    echo "Checking: $p" . PHP_EOL;
    echo " exists: " . (file_exists($p) ? 'yes' : 'no') . PHP_EOL;
    echo " readable: " . (is_readable($p) ? 'yes' : 'no') . PHP_EOL;
    if (file_exists($p)) {
        $perms = fileperms($p);
        echo " perms (octal): " . substr(sprintf('%o', $perms), -4) . PHP_EOL;
        if (function_exists('posix_getpwuid')) {
            $info = posix_getpwuid(fileowner($p));
            echo " owner: " . ($info['name'] ?? fileowner($p)) . PHP_EOL;
        } else {
            echo " owner UID: " . fileowner($p) . PHP_EOL;
        }
    }
    echo PHP_EOL;
}

echo 'getenv GOOGLE_MAPS_KEY: ' . (getenv('GOOGLE_MAPS_KEY') ? 'present' : 'missing') . PHP_EOL;
echo 'getenv RECAPTCHA_SITE_KEY: ' . (getenv('RECAPTCHA_SITE_KEY') ? 'present' : 'missing') . PHP_EOL;
echo 'getenv GRECAPTCHA_SECRET_KEY: ' . (getenv('GRECAPTCHA_SECRET_KEY') ? 'present' : 'missing') . PHP_EOL;

echo '_ENV keys: ' . (isset($_ENV['GOOGLE_MAPS_KEY']) ? 'maps in $_ENV ' : '') . (isset($_ENV['RECAPTCHA_SITE_KEY']) ? 'recaptcha in $_ENV' : '') . PHP_EOL;

echo "\nREMOVE THIS FILE AFTER TESTING.\n";
?>
