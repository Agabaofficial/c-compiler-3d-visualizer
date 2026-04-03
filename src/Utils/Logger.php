<?php

namespace App\Utils;

/**
 * File-based PSR-3-inspired logger.
 */
class Logger
{
    private const LEVELS = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];

    private string $errorLogFile;
    private string $accessLogFile;
    private int    $minLevel;

    public function __construct(?string $errorLogFile = null, ?string $accessLogFile = null)
    {
        $this->errorLogFile  = $errorLogFile  ?? (defined('LOG_FILE')        ? LOG_FILE        : dirname(dirname(__DIR__)) . '/logs/error.log');
        $this->accessLogFile = $accessLogFile ?? (defined('ACCESS_LOG_FILE') ? ACCESS_LOG_FILE : dirname(dirname(__DIR__)) . '/logs/access.log');

        $configLevel    = defined('LOG_LEVEL') ? LOG_LEVEL : (getenv('LOG_LEVEL') ?: 'error');
        $this->minLevel = self::LEVELS[strtolower($configLevel)] ?? self::LEVELS['error'];

        $this->ensureLogDir($this->errorLogFile);
        $this->ensureLogDir($this->accessLogFile);
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    public function log(string $level, string $message, array $context = []): void
    {
        $numericLevel = self::LEVELS[strtolower($level)] ?? self::LEVELS['info'];
        if ($numericLevel < $this->minLevel) {
            return;
        }

        $entry = $this->formatEntry($level, $message, $context);

        if (strtolower($level) === 'info') {
            $this->writeToFile($this->accessLogFile, $entry);
        } else {
            $this->writeToFile($this->errorLogFile, $entry);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log an HTTP access entry (always written to the access log).
     */
    public function access(string $message, array $context = []): void
    {
        $context['_access'] = true;
        $entry = $this->formatEntry('access', $message, $context);
        $this->writeToFile($this->accessLogFile, $entry);
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function formatEntry(string $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $ip        = $_SERVER['REMOTE_ADDR']     ?? 'cli';
        $method    = $_SERVER['REQUEST_METHOD']  ?? '';
        $uri       = $_SERVER['REQUEST_URI']     ?? '';
        $ctxStr    = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return "[{$timestamp}] [{$level}] [{$ip}] [{$method} {$uri}] {$message}{$ctxStr}" . PHP_EOL;
    }

    private function writeToFile(string $path, string $entry): void
    {
        // Use file locking to avoid race conditions under concurrent requests
        $fp = @fopen($path, 'a');
        if ($fp === false) {
            return;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $entry);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    private function ensureLogDir(string $filePath): void
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}
