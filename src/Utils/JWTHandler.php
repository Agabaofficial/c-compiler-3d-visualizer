<?php

namespace App\Utils;

use Exception;
use RuntimeException;

/**
 * Pure-PHP HMAC-SHA256 JWT handler (no external libraries).
 *
 * Token format: base64url(header).base64url(payload).base64url(signature)
 */
class JWTHandler
{
    private string $secret;
    private int    $expiration;

    public function __construct(?string $secret = null, ?int $expiration = null)
    {
        $this->secret     = $secret     ?? (defined('JWT_SECRET')     ? JWT_SECRET     : (getenv('JWT_SECRET')     ?: 'change_me_in_production_min_32_chars!!'));
        $this->expiration = $expiration ?? (defined('JWT_EXPIRATION') ? JWT_EXPIRATION : (int)(getenv('JWT_EXPIRATION') ?: 3600));
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Generate a signed JWT for $payload.
     *
     * @param array $payload   Claims to encode (iat, exp added automatically if missing)
     * @param int|null $expiration Override default expiration (seconds from now)
     */
    public function generate(array $payload, ?int $expiration = null): string
    {
        $now = time();
        $payload['iat'] = $payload['iat'] ?? $now;
        $payload['exp'] = $payload['exp'] ?? ($now + ($expiration ?? $this->expiration));

        $header    = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body      = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->sign("{$header}.{$body}");

        return "{$header}.{$body}.{$signature}";
    }

    /**
     * Validate a JWT and return its decoded payload.
     *
     * @throws RuntimeException on any validation failure
     */
    public function validate(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token format');
        }

        [$header, $body, $signature] = $parts;

        // Verify signature
        $expectedSignature = $this->sign("{$header}.{$body}");
        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Invalid token signature');
        }

        $payload = json_decode($this->base64UrlDecode($body), true);
        if (!is_array($payload)) {
            throw new RuntimeException('Invalid token payload');
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new RuntimeException('Token has expired');
        }

        return $payload;
    }

    /**
     * Generate a cryptographically secure refresh token (hex string).
     */
    public function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function sign(string $data): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $data, $this->secret, true));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
