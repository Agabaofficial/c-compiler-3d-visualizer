<?php
/**
 * config/constants.php
 * Thin bootstrap shim – simply delegates to src/Config/Constants.php.
 * Kept here so legacy includes of config/constants.php still work.
 */

$_configRoot = dirname(__FILE__);
$_srcConfig  = dirname($_configRoot) . '/src/Config/Constants.php';

if (file_exists($_srcConfig)) {
    require_once $_srcConfig;
    \App\Config\Constants::load();
} else {
    // Minimal fallback: load .env directly from this directory
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        $envFile = __DIR__ . '/.env.example';
    }
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
