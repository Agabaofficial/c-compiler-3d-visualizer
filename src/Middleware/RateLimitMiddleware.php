<?php

namespace App\Middleware;

use App\Utils\Logger;

/**
 * RateLimitMiddleware – file-based token-bucket rate limiter.
 *
 * Uses a small JSON file per identifier stored in the logs/ directory
 * (or a configurable directory) so that Redis is not required.
 */
class RateLimitMiddleware
{
    private Logger $logger;
    private string $storageDir;
    private int    $defaultLimit;
    private int    $defaultWindow;

    public function __construct()
    {
        $this->logger        = new Logger();
        $this->defaultLimit  = defined('RATE_LIMIT_REQUESTS') ? RATE_LIMIT_REQUESTS : 100;
        $this->defaultWindow = defined('RATE_LIMIT_WINDOW')   ? RATE_LIMIT_WINDOW   : 60;

        // Store rate-limit state files in logs/rl/ (inside the project, not /tmp)
        $root             = defined('APP_ROOT') ? APP_ROOT : dirname(dirname(dirname(__FILE__)));
        $this->storageDir = $root . '/logs/rl';

        if (!is_dir($this->storageDir)) {
            @mkdir($this->storageDir, 0700, true);
        }
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Check whether the given identifier has exceeded the rate limit.
     *
     * @param string   $identifier Typically an IP address or user ID
     * @param int|null $limit      Max requests (defaults to RATE_LIMIT_REQUESTS)
     * @param int|null $window     Window in seconds (defaults to RATE_LIMIT_WINDOW)
     * @return bool  true = allowed, false = rate limited
     */
    public function check(string $identifier, ?int $limit = null, ?int $window = null): bool
    {
        $limit  = $limit  ?? $this->defaultLimit;
        $window = $window ?? $this->defaultWindow;

        $data = $this->loadData($identifier);
        $now  = time();

        // Reset window if expired
        if ($data['reset_at'] <= $now) {
            $data = [
                'requests' => 0,
                'reset_at' => $now + $window,
            ];
        }

        $data['requests']++;
        $this->saveData($identifier, $data);

        return $data['requests'] <= $limit;
    }

    /**
     * Return the number of remaining requests in the current window.
     */
    public function getRemainingRequests(string $identifier, ?int $limit = null): int
    {
        $limit = $limit ?? $this->defaultLimit;
        $data  = $this->loadData($identifier);

        if ($data['reset_at'] <= time()) {
            return $limit;
        }

        return max(0, $limit - (int) $data['requests']);
    }

    /**
     * Return the Unix timestamp at which the rate limit window resets.
     */
    public function getResetTime(string $identifier): int
    {
        $data = $this->loadData($identifier);
        return (int) $data['reset_at'];
    }

    /**
     * Run rate limit check using the request IP (or $identifier if provided).
     * Emits a 429 JSON response and exits if the limit is exceeded.
     */
    public function handle(?string $identifier = null): void
    {
        $identifier = $identifier ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        $allowed   = $this->check($identifier);
        $remaining = $this->getRemainingRequests($identifier);
        $resetAt   = $this->getResetTime($identifier);

        header('X-RateLimit-Limit: '     . $this->defaultLimit);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: '     . $resetAt);

        if (!$allowed) {
            $this->logger->warning('Rate limit exceeded', ['identifier' => $identifier]);
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: ' . max(0, $resetAt - time()));
            echo json_encode([
                'success' => false,
                'error'   => 'Too many requests. Please try again later.',
                'code'    => 429,
            ]);
            exit;
        }
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function filePath(string $identifier): string
    {
        return $this->storageDir . '/' . hash('sha256', $identifier) . '.json';
    }

    private function loadData(string $identifier): array
    {
        $path = $this->filePath($identifier);
        if (!file_exists($path)) {
            return ['requests' => 0, 'reset_at' => 0];
        }

        $fp      = @fopen($path, 'r');
        $content = '';
        if ($fp) {
            flock($fp, LOCK_SH);
            $content = stream_get_contents($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : ['requests' => 0, 'reset_at' => 0];
    }

    private function saveData(string $identifier, array $data): void
    {
        $path = $this->filePath($identifier);
        $fp   = @fopen($path, 'w');
        if ($fp) {
            flock($fp, LOCK_EX);
            fwrite($fp, json_encode($data));
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}
