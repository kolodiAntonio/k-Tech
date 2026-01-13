<?php
// env-diag.php - locate .env in parent dirs (web-root copy)
// IMPORTANT: remove this file after testing
$keys = [
    'GOOGLE_MAPS_KEY', 'GOOGLE_MAPS_API_KEY',
    'RECAPTCHA_SITE_KEY', 'RECAPTCHA_PUBLIC_KEY', 'RECAPTCHA_SITEKEY',
    'GRECAPTCHA_SITE_KEY', 'GRECAPTCHA_SECRET_KEY', 'RECAPTCHA_SECRET_KEY', 'GOOGLE_RECAPTCHA_SECRET',
];
function short($v){ if ($v === null || $v === false || $v === '') return '(missing)'; $v = trim($v); return strlen($v) > 12 ? substr($v,0,12) . '...' : $v; }
header('Content-Type: text/plain; charset=utf-8');
echo "env-diag.php - web-root diagnostics\n\n";
$cwd = getcwd();
echo "Current working dir: $cwd\n\n";
$found = [];
$dir = $cwd;
for ($i=0;$i<8;$i++){
    $path = $dir . DIRECTORY_SEPARATOR . '.env';
    if (file_exists($path)){
        $info = ['path'=>$path,'readable'=>is_readable($path),'size'=>filesize($path),'perms'=>substr(sprintf('%o', fileperms($path)),-4)];
        if ($info['readable']){
            $content = file_get_contents($path, false, null, 0, 8192);
            $lines = preg_split('/\r?\n/',$content);
            $pairs = [];
            foreach ($lines as $ln){
                $ln = trim($ln);
                if ($ln === '' || strpos($ln,'#') === 0) continue;
                if (!strpos($ln,'=')) continue;
                list($k,$v) = explode('=',$ln,2);
                $k = trim($k);
                $v = trim($v);
                $v = preg_replace('/^["\']|["\']$/','',$v);
                $pairs[$k] = $v;
            }
            $info['pairs'] = $pairs;
        } else { $info['pairs'] = null; }
        $found[] = $info;
    }
    $parent = dirname($dir); if ($parent === $dir) break; $dir = $parent;
}
if (count($found)===0){ echo "No .env found in current dir or up to 7 parents.\n\n"; }
else { foreach ($found as $f){ echo "Found: " . $f['path'] . "\n"; echo "  Readable: " . ($f['readable'] ? 'yes' : 'no') . "\n"; echo "  Size: " . $f['size'] . " bytes\n"; echo "  Perms: " . $f['perms'] . "\n"; if ($f['pairs'] !== null){ echo "  Keys (first 12 chars):\n"; foreach ($keys as $k){ if (array_key_exists($k,$f['pairs'])) echo "    $k = " . short($f['pairs'][$k]) . "\n"; } } else { echo "  (file not readable)\n"; } echo "\n"; } }

echo "Runtime sources:\n";
foreach ($keys as $k){ $g = getenv($k); $from = 'none'; if ($g !== false) $from='getenv'; elseif (isset($_ENV[$k])) $from='_ENV'; elseif (isset($_SERVER[$k])) $from='_SERVER'; $val = $g !== false ? $g : (isset($_ENV[$k]) ? $_ENV[$k] : (isset($_SERVER[$k]) ? $_SERVER[$k] : null)); echo sprintf("%s : %s (source: %s)\n", $k, short($val), $from); }

echo "\nPHP SAPI: " . php_sapi_name() . "\n";
echo "Loaded php.ini: " . (php_ini_loaded_file() ?: '(none)') . "\n";
echo "\nIMPORTANT: remove this file from the server after testing.\n";
?>