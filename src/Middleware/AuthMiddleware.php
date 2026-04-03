<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Utils\Logger;
use RuntimeException;

/**
 * AuthMiddleware – JWT-based request authentication.
 */
class AuthMiddleware
{
    private AuthService $authService;
    private Logger      $logger;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->logger      = new Logger();
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Extract and validate the Bearer token from the Authorization header.
     * Sets $GLOBALS['user'] on success.
     *
     * @return array|null Decoded token payload, or null on failure
     */
    public function handle(): ?array
    {
        $token = $this->extractBearerToken();
        if ($token === null) {
            return null;
        }

        try {
            $payload              = $this->authService->validateToken($token);
            $GLOBALS['user']      = $payload;
            $GLOBALS['user_id']   = (int) ($payload['sub'] ?? 0);
            $GLOBALS['user_role'] = $payload['role'] ?? 'user';
            return $payload;
        } catch (RuntimeException $e) {
            $this->logger->debug('Token validation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Require a valid JWT.  Outputs a 401 JSON error and exits if missing/invalid.
     */
    public function requireAuth(): array
    {
        $payload = $this->handle();
        if ($payload === null) {
            $this->unauthorised('Authentication required');
        }
        return $payload;
    }

    /**
     * Require a valid JWT with role = admin.
     * Outputs 401 or 403 and exits on failure.
     */
    public function requireAdmin(): array
    {
        $payload = $this->requireAuth();
        if (($payload['role'] ?? '') !== 'admin') {
            $this->forbidden('Admin privileges required');
        }
        return $payload;
    }

    /**
     * Set user context if a valid token is present; do not reject if missing.
     */
    public function optionalAuth(): ?array
    {
        return $this->handle();
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function extractBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if ($header === '' && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header  = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (strncasecmp($header, 'Bearer ', 7) === 0) {
            return trim(substr($header, 7));
        }

        return null;
    }

    private function unauthorised(string $message): never
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message, 'code' => 401]);
        exit;
    }

    private function forbidden(string $message): never
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message, 'code' => 403]);
        exit;
    }
}
