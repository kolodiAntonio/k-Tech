<?php
// Temporary debug file — remove immediately after testing.
// Only prints obscured/partial values to avoid leaking secrets.
header('Content-Type: text/plain; charset=utf-8');

$keys = [
    'GOOGLE_MAPS_KEY',
    'RECAPTCHA_SITE_KEY',
    'GRECAPTCHA_SECRET_KEY'
];

foreach ($keys as $k) {
    $v_getenv = getenv($k);
    $v_env = isset($_ENV[$k]) ? $_ENV[$k] : null;
    $v_server = isset($_SERVER[$k]) ? $_SERVER[$k] : null;

    // choose first non-empty source
    $val = $v_getenv ?: $v_env ?: $v_server;

    if (!$val) {
        echo "$k: (not set)\n";
        continue;
    }

    // show only first 8 chars for safety
    $safe = substr($val, 0, 8) . '...';
    // mark whether value came from getenv/$_ENV/$_SERVER
    $source = $v_getenv ? 'getenv' : ($v_env ? '$_ENV' : '$_SERVER');
    echo "$k: $safe  ($source)\n";
}

echo "\nREMOVE THIS FILE AFTER TESTING.\n";
?>
<?php
// Temporary debug file — remove this immediately after testing
// Shows only the first 8 characters of site keys, never the secret.
header('Content-Type: text/plain; charset=utf-8');

// getenv()
$maps_getenv = getenv('GOOGLE_MAPS_KEY') ?: '';
$site_getenv = getenv('RECAPTCHA_SITE_KEY') ?: '';
$secret_getenv = getenv('GRECAPTCHA_SECRET_KEY') ?: '';

// 
$maps_env = isset($_ENV['GOOGLE_MAPS_KEY']) ? $_ENV['GOOGLE_MAPS_KEY'] : '';
$site_env = isset($_ENV['RECAPTCHA_SITE_KEY']) ? $_ENV['RECAPTCHA_SITE_KEY'] : '';
$secret_env = isset($_ENV['GRECAPTCHA_SECRET_KEY']) ? $_ENV['GRECAPTCHA_SECRET_KEY'] : '';

$maps_server = isset($_SERVER['GOOGLE_MAPS_KEY']) ? $_SERVER['GOOGLE_MAPS_KEY'] : '';
$site_server = isset($_SERVER['RECAPTCHA_SITE_KEY']) ? $_SERVER['RECAPTCHA_SITE_KEY'] : '';
$secret_server = isset($_SERVER['GRECAPTCHA_SECRET_KEY']) ? $_SERVER['GRECAPTCHA_SECRET_KEY'] : '';

echo "getenv() results:\n";
echo 'MAPS: ' . ( $maps_getenv ? substr($maps_getenv, 0, 8) . '...' : 'none' ) . "\n";
echo 'RECAPTCHA SITE: ' . ( $site_getenv ? substr($site_getenv, 0, 8) . '...' : 'none' ) . "\n";
echo 'GRECAPTCHA_SECRET present: ' . ( $secret_getenv ? 'yes' : 'no' ) . "\n\n";

echo "\
\$_ENV results:\n";
echo 'MAPS: ' . ( $maps_env ? substr($maps_env, 0, 8) . '...' : 'none' ) . "\n";
echo 'RECAPTCHA SITE: ' . ( $site_env ? substr($site_env, 0, 8) . '...' : 'none' ) . "\n";
echo 'GRECAPTCHA_SECRET present: ' . ( $secret_env ? 'yes' : 'no' ) . "\n\n";

echo "\
\
\
\
\
\
\
\
\
\$_SERVER results:\n";
echo 'MAPS: ' . ( $maps_server ? substr($maps_server, 0, 8) . '...' : 'none' ) . "\n";
echo 'RECAPTCHA SITE: ' . ( $site_server ? substr($site_server, 0, 8) . '...' : 'none' ) . "\n";
echo 'GRECAPTCHA_SECRET present: ' . ( $secret_server ? 'yes' : 'no' ) . "\n\n";

echo "REMOVE THIS FILE AFTER TESTING.\n";
?>
