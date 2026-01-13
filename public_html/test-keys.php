<?php
// Temporary debug file — remove this immediately after testing
// Shows only the first 8 characters of site keys, never the secret.
$maps = getenv('GOOGLE_MAPS_KEY') ?: '';
$site = getenv('RECAPTCHA_SITE_KEY') ?: '';
$secret_present = getenv('GRECAPTCHA_SECRET_KEY') ? 'present' : 'missing';
header('Content-Type: text/plain; charset=utf-8');
echo 'MAPS: ' . ( $maps ? substr($maps, 0, 8) . '...' : 'none' ) . "\n";
echo 'RECAPTCHA SITE: ' . ( $site ? substr($site, 0, 8) . '...' : 'none' ) . "\n";
echo 'GRECAPTCHA_SECRET: ' . $secret_present . "\n";
echo "\nREMOVE THIS FILE AFTER TESTING.\n";
?>
