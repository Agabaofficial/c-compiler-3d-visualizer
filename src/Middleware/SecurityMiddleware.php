<?php

namespace App\Middleware;

use App\Utils\Validator;
use App\Utils\Logger;

/**
 * SecurityMiddleware – CORS, security headers, CSRF, input sanitisation.
 */
class SecurityMiddleware
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Run all security checks appropriate for an API request.
     * Call this at the very top of each entry-point file.
     */
    public function handle(): void
    {
        $this->setCORSHeaders();
        $this->setSecurityHeaders();

        // Handle CORS preflight – no further processing needed
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /**
     * Set CORS response headers.
     * In production the allowed origins are taken from CORS_ALLOWED_ORIGINS.
     */
    public function setCORSHeaders(): void
    {
        $allowedOrigins = defined('CORS_ALLOWED_ORIGINS')
            ? CORS_ALLOWED_ORIGINS
            : (getenv('CORS_ALLOWED_ORIGINS') ?: '*');

        $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($allowedOrigins === '*') {
            header('Access-Control-Allow-Origin: *');
        } elseif ($requestOrigin !== '') {
            $originList = array_map('trim', explode(',', $allowedOrigins));
            if (in_array($requestOrigin, $originList, true)) {
                header("Access-Control-Allow-Origin: {$requestOrigin}");
                header('Vary: Origin');
            }
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }

    /**
     * Set standard security response headers.
     */
    public function setSecurityHeaders(): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'");
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
        // Remove the PHP version fingerprint
        header_remove('X-Powered-By');
    }

    /**
     * Validate the X-CSRF-Token header against the value stored in the session.
     * Returns false (and optionally sends a 403) if validation fails.
     *
     * @param bool $abort Send a 403 JSON response and exit on failure
     */
    public function validateCSRFToken(bool $abort = false): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if ($sessionToken === '' || !hash_equals($sessionToken, $requestToken)) {
            if ($abort) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'CSRF token validation failed', 'code' => 403]);
                exit;
            }
            return false;
        }

        return true;
    }

    /**
     * Recursively sanitise an array of input data in-place.
     */
    public function sanitizeInput(array &$data): void
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->sanitizeInput($value);
            } elseif (is_string($value)) {
                $value = Validator::sanitizeString($value);
            }
        }
        unset($value);
    }

    /**
     * Check submitted code for dangerous PHP functions.
     * Returns true if the code is considered safe.
     */
    public function preventCodeInjection(string $code): bool
    {
        $dangerousPatterns = [
            '/\beval\s*\(/i',
            '/\bexec\s*\(/i',
            '/\bshell_exec\s*\(/i',
            '/\bsystem\s*\(/i',
            '/\bpassthru\s*\(/i',
            '/\bpopen\s*\(/i',
            '/\bproc_open\s*\(/i',
            '/`[^`]*`/',           // backtick operator
            '/\bbase64_decode\s*\(/i',
            '/\bfile_get_contents\s*\(/i',
            '/\bunlink\s*\(/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $code)) {
                $this->logger->warning('Potential code injection detected', [
                    'pattern' => $pattern,
                    'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
                return false;
            }
        }

        return true;
    }
}
