<?php

namespace App\Services;

use App\Config\Database;
use App\Models\User;
use App\Utils\JWTHandler;
use App\Utils\Validator;
use PDO;
use RuntimeException;

/**
 * AuthService – registration, login, token refresh, logout, password reset.
 */
class AuthService
{
    private User       $userModel;
    private JWTHandler $jwt;
    private PDO        $db;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jwt       = new JWTHandler();
        $this->db        = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Register a new user and return JWT + refresh token.
     *
     * @throws RuntimeException on validation / duplicate failures
     */
    public function register(string $username, string $email, string $password): array
    {
        // Validate inputs
        $errors = Validator::validate(
            ['username' => $username, 'email' => $email, 'password' => $password],
            [
                'username' => ['required', 'username'],
                'email'    => ['required', 'email'],
                'password' => ['required', 'password'],
            ]
        );
        if (!empty($errors)) {
            throw new RuntimeException(implode('; ', $errors), 422);
        }

        // Check duplicates
        if ($this->userModel->findByEmail($email)) {
            throw new RuntimeException('Email address already registered', 409);
        }

        $id = $this->userModel->create($username, $email, $password);
        $this->userModel->updateLastLogin($id);

        return $this->issueTokens($id, 'user');
    }

    /**
     * Authenticate by email/password and return JWT + refresh token.
     *
     * @throws RuntimeException on invalid credentials or banned account
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('Invalid email or password', 401);
        }

        if ((int) $user['is_banned'] === 1) {
            throw new RuntimeException('Account has been suspended', 403);
        }

        $this->userModel->updateLastLogin((int) $user['id']);

        return $this->issueTokens((int) $user['id'], $user['role']);
    }

    /**
     * Exchange a valid refresh token for a new access token.
     *
     * @throws RuntimeException if token is invalid / expired
     */
    public function refresh(string $refreshToken): array
    {
        $tokenHash = hash('sha256', $refreshToken);

        $stmt = $this->db->prepare(
            'SELECT rt.*, u.role
             FROM refresh_tokens rt
             JOIN users u ON u.id = rt.user_id
             WHERE rt.token_hash = :hash
               AND rt.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([':hash' => $tokenHash]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException('Invalid or expired refresh token', 401);
        }

        // Rotate: delete old token, issue new pair
        $this->revokeRefreshToken($tokenHash);
        return $this->issueTokens((int) $row['user_id'], $row['role']);
    }

    /**
     * Invalidate a refresh token on logout.
     */
    public function logout(int $userId, string $refreshToken): bool
    {
        $tokenHash = hash('sha256', $refreshToken);
        return $this->revokeRefreshToken($tokenHash);
    }

    /**
     * Validate an access token and return its payload.
     *
     * @throws RuntimeException on invalid token
     */
    public function validateToken(string $token): array
    {
        return $this->jwt->validate($token);
    }

    /**
     * Generate a password-reset token and return it (caller must email it).
     * Token is stored hashed in the refresh_tokens table with a 1-hour expiry,
     * reusing the same infrastructure to keep things simple.
     *
     * @throws RuntimeException if email not found
     */
    public function generatePasswordReset(string $email): string
    {
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            // Don't leak whether the email exists
            throw new RuntimeException('If that email is registered you will receive a reset link', 200);
        }

        $token     = $this->jwt->generateRefreshToken();
        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->prepare(
            'INSERT INTO refresh_tokens (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
        );
        $stmt->execute([':user_id' => $user['id'], ':token_hash' => $tokenHash]);

        return $token;
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function issueTokens(int $userId, string $role): array
    {
        $refreshExpiry = defined('JWT_REFRESH_EXPIRATION') ? JWT_REFRESH_EXPIRATION : 604800;

        // Access token
        $accessToken = $this->jwt->generate([
            'sub'  => $userId,
            'role' => $role,
        ]);

        // Refresh token (stored hashed)
        $refreshToken = $this->jwt->generateRefreshToken();
        $tokenHash    = hash('sha256', $refreshToken);

        $stmt = $this->db->prepare(
            'INSERT INTO refresh_tokens (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL :expiry SECOND))'
        );
        $stmt->execute([
            ':user_id'    => $userId,
            ':token_hash' => $tokenHash,
            ':expiry'     => $refreshExpiry,
        ]);

        $expiration = defined('JWT_EXPIRATION') ? JWT_EXPIRATION : 3600;

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => $expiration,
            'user_id'       => $userId,
            'role'          => $role,
        ];
    }

    private function revokeRefreshToken(string $tokenHash): bool
    {
        $stmt = $this->db->prepare('DELETE FROM refresh_tokens WHERE token_hash = :hash');
        return $stmt->execute([':hash' => $tokenHash]);
    }
}
