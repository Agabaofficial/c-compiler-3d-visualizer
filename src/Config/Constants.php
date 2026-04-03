<?php

namespace App\Config;

/**
 * Constants – loads the .env file and defines application-wide PHP constants.
 */
class Constants
{
    private static bool $loaded = false;

    /**
     * Load environment variables from config/.env (falls back to config/.env.example).
     * Safe to call multiple times; subsequent calls are no-ops.
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $root    = self::appRoot();
        $envFile = $root . '/config/.env';
        if (!file_exists($envFile)) {
            $envFile = $root . '/config/.env.example';
        }

        if (file_exists($envFile)) {
            self::parseEnvFile($envFile);
        }

        self::defineConstants($root);
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private static function appRoot(): string
    {
        // src/Config/Constants.php → src/Config → src → project root
        return dirname(dirname(dirname(__FILE__)));
    }

    private static function parseEnvFile(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

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

            // Strip surrounding quotes if present
            if (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
                $value = substr($value, 1, -1);
            } elseif (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
                $value = substr($value, 1, -1);
            }

            // Only set if not already present in the environment
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    private static function defineConstants(string $root): void
    {
        defined('APP_ROOT')    || define('APP_ROOT',    $root);
        defined('BASE_URL')    || define('BASE_URL',    self::env('FRONTEND_URL', 'http://localhost'));
        defined('API_URL')     || define('API_URL',     self::env('API_URL',      'http://localhost/api/v1'));
        defined('API_VERSION') || define('API_VERSION', self::env('API_VERSION',  'v1'));

        // Database
        defined('DB_HOST') || define('DB_HOST', self::env('DB_HOST', 'localhost'));
        defined('DB_PORT') || define('DB_PORT', (int) self::env('DB_PORT', '3306'));
        defined('DB_USER') || define('DB_USER', self::env('DB_USER', 'root'));
        defined('DB_PASS') || define('DB_PASS', self::env('DB_PASS', ''));
        defined('DB_NAME') || define('DB_NAME', self::env('DB_NAME', 'compilerhub'));

        // JWT
        defined('JWT_SECRET')             || define('JWT_SECRET',             self::env('JWT_SECRET', 'change_me_in_production_min_32_chars!!'));
        defined('JWT_EXPIRATION')         || define('JWT_EXPIRATION',         (int) self::env('JWT_EXPIRATION', '3600'));
        defined('JWT_REFRESH_EXPIRATION') || define('JWT_REFRESH_EXPIRATION', (int) self::env('JWT_REFRESH_EXPIRATION', '604800'));

        // Redis
        defined('REDIS_HOST')     || define('REDIS_HOST',     self::env('REDIS_HOST', 'localhost'));
        defined('REDIS_PORT')     || define('REDIS_PORT',     (int) self::env('REDIS_PORT', '6379'));
        defined('REDIS_PASSWORD') || define('REDIS_PASSWORD', self::env('REDIS_PASSWORD', ''));
        defined('REDIS_DB')       || define('REDIS_DB',       (int) self::env('REDIS_DB', '0'));

        // Rate limiting
        defined('RATE_LIMIT_REQUESTS') || define('RATE_LIMIT_REQUESTS', (int) self::env('RATE_LIMIT_REQUESTS', '100'));
        defined('RATE_LIMIT_WINDOW')   || define('RATE_LIMIT_WINDOW',   (int) self::env('RATE_LIMIT_WINDOW',   '60'));

        // Logging
        defined('LOG_LEVEL')       || define('LOG_LEVEL',       self::env('LOG_LEVEL',       'error'));
        defined('LOG_FILE')        || define('LOG_FILE',        $root . '/' . self::env('LOG_FILE',        'logs/error.log'));
        defined('ACCESS_LOG_FILE') || define('ACCESS_LOG_FILE', $root . '/' . self::env('ACCESS_LOG_FILE', 'logs/access.log'));
        defined('DEBUG_MODE')      || define('DEBUG_MODE',      self::env('DEBUG_MODE', 'false') === 'true');

        // CORS
        defined('CORS_ALLOWED_ORIGINS') || define('CORS_ALLOWED_ORIGINS', self::env('CORS_ALLOWED_ORIGINS', '*'));
    }

    /**
     * Retrieve an environment variable or return $default.
     */
    public static function env(string $key, string $default = ''): string
    {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        return $_ENV[$key] ?? $default;
    }
}
