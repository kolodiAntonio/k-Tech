<?php
// test-keys.php - diagnostic for environment keys
// Prints first 8 chars of common keys and where they were found (getenv, _ENV, _SERVER)
$keys = [
    'GOOGLE_MAPS_KEY',
    'GOOGLE_MAPS_API_KEY',
    'RECAPTCHA_SITE_KEY',
    'RECAPTCHA_PUBLIC_KEY',
    'RECAPTCHA_SITEKEY',
    'GRECAPTCHA_SITE_KEY',
    'GRECAPTCHA_SECRET_KEY',
    'RECAPTCHA_SECRET_KEY',
    'GOOGLE_RECAPTCHA_SECRET',
];
function fetch_val($name){
    $val = getenv($name);
    if ($val !== false) return ["value"=>$val, "src"=>"getenv"];
    if (isset($_ENV[$name])) return ["value"=>$_ENV[$name], "src"=>"_ENV"];
    if (isset($_SERVER[$name])) return ["value"=>$_SERVER[$name], "src"=>"_SERVER"];
    return ["value"=>null, "src"=>"none"];
}
header('Content-Type: text/plain; charset=utf-8');
echo "test-keys.php - environment diagnostics\n\n";
foreach ($keys as $k) {
    $r = fetch_val($k);
    $val = $r['value'];
    $src = $r['src'];
    $display = $val ? (substr($val,0,8) . (strlen($val) > 8 ? '...' : '')) : '(missing)';
    echo sprintf("%s: %s (source: %s)\n", $k, $display, $src);
}

echo "\nServer PHP info summary:\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "Current working dir: " . getcwd() . "\n";
echo "SCRIPT_FILENAME: " . (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '(none)') . "\n";
?>
