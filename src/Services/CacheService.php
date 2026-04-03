<?php

namespace App\Services;

use App\Utils\Logger;

/**
 * CacheService – Redis-backed cache with graceful fallback when Redis is unavailable.
 */
class CacheService
{
    private mixed  $redis = null;
    private bool   $available = false;
    private Logger $logger;

    private string $prefix = 'compilerhub:';

    public function __construct()
    {
        $this->logger = new Logger();
        $this->connect();
    }

    // ------------------------------------------------------------------
    // Generic cache operations
    // ------------------------------------------------------------------

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        if (!$this->available) {
            return false;
        }
        try {
            $serialized = json_encode($value);
            return (bool) $this->redis->setex($this->prefix . $key, $ttl, $serialized);
        } catch (\Throwable $e) {
            $this->logger->warning('CacheService::set failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function get(string $key): mixed
    {
        if (!$this->available) {
            return null;
        }
        try {
            $val = $this->redis->get($this->prefix . $key);
            if ($val === false) {
                return null;
            }
            return json_decode($val, true);
        } catch (\Throwable $e) {
            $this->logger->warning('CacheService::get failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function delete(string $key): bool
    {
        if (!$this->available) {
            return false;
        }
        try {
            return (bool) $this->redis->del($this->prefix . $key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function exists(string $key): bool
    {
        if (!$this->available) {
            return false;
        }
        try {
            return (bool) $this->redis->exists($this->prefix . $key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function flush(): bool
    {
        if (!$this->available) {
            return false;
        }
        try {
            return (bool) $this->redis->flushDB();
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Domain-specific helpers
    // ------------------------------------------------------------------

    public function cacheCompilationResult(string $codeHash, mixed $data, int $ttl = 3600): bool
    {
        return $this->set("compile:{$codeHash}", $data, $ttl);
    }

    public function getCachedResult(string $codeHash): mixed
    {
        return $this->get("compile:{$codeHash}");
    }

    public function cacheUserSession(int $userId, array $data, int $ttl = 3600): bool
    {
        return $this->set("session:{$userId}", $data, $ttl);
    }

    public function getUserSession(int $userId): ?array
    {
        $val = $this->get("session:{$userId}");
        return is_array($val) ? $val : null;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function connect(): void
    {
        if (!extension_loaded('redis')) {
            $this->logger->warning('Redis extension not loaded; cache disabled');
            return;
        }

        $host     = defined('REDIS_HOST')     ? REDIS_HOST     : (getenv('REDIS_HOST')     ?: 'localhost');
        $port     = defined('REDIS_PORT')     ? REDIS_PORT     : (int)(getenv('REDIS_PORT') ?: 6379);
        $password = defined('REDIS_PASSWORD') ? REDIS_PASSWORD : (getenv('REDIS_PASSWORD') ?: '');
        $db       = defined('REDIS_DB')       ? REDIS_DB       : (int)(getenv('REDIS_DB')   ?: 0);

        try {
            $redis = new \Redis();
            $redis->connect($host, $port, 2.0); // 2 second timeout

            if ($password !== '') {
                $redis->auth($password);
            }

            $redis->select($db);
            $redis->ping();

            $this->redis     = $redis;
            $this->available = true;
        } catch (\Throwable $e) {
            $this->logger->warning('Redis connection failed; cache disabled', ['error' => $e->getMessage()]);
        }
    }
}
