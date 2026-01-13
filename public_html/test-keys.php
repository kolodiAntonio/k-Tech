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
