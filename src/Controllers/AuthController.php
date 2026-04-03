<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Utils\Validator;
use RuntimeException;

/**
 * AuthController – register, login, refresh, logout.
 */
class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    // ------------------------------------------------------------------
    // Endpoints
    // ------------------------------------------------------------------

    public function register(): void
    {
        $body = $this->parseJsonBody();

        $username = trim($body['username'] ?? '');
        $email    = trim($body['email']    ?? '');
        $password = $body['password']      ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $this->error(400, 'username, email and password are required');
        }

        try {
            $tokens = $this->authService->register($username, $email, $password);
            $this->success($tokens, 'Registration successful', 201);
        } catch (RuntimeException $e) {
            $this->error($e->getCode() ?: 400, $e->getMessage());
        }
    }

    public function login(): void
    {
        $body = $this->parseJsonBody();

        $email    = trim($body['email']    ?? '');
        $password = $body['password']      ?? '';

        if ($email === '' || $password === '') {
            $this->error(400, 'email and password are required');
        }

        try {
            $tokens = $this->authService->login($email, $password);
            $this->success($tokens, 'Login successful');
        } catch (RuntimeException $e) {
            $this->error($e->getCode() ?: 401, $e->getMessage());
        }
    }

    public function refresh(): void
    {
        $body = $this->parseJsonBody();
        $refreshToken = trim($body['refresh_token'] ?? '');

        if ($refreshToken === '') {
            $this->error(400, 'refresh_token is required');
        }

        try {
            $tokens = $this->authService->refresh($refreshToken);
            $this->success($tokens, 'Token refreshed');
        } catch (RuntimeException $e) {
            $this->error($e->getCode() ?: 401, $e->getMessage());
        }
    }

    public function logout(): void
    {
        $userId       = (int) ($GLOBALS['user_id'] ?? 0);
        $body         = $this->parseJsonBody();
        $refreshToken = trim($body['refresh_token'] ?? '');

        if ($userId === 0) {
            $this->error(401, 'Authentication required');
        }

        if ($refreshToken !== '') {
            $this->authService->logout($userId, $refreshToken);
        }

        $this->success([], 'Logged out successfully');
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function parseJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) {
            return $_POST ?: [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function success(array $data, string $message = 'OK', int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data, 'message' => $message]);
        exit;
    }

    private function error(int $code, string $message): never
    {
        $httpCode = ($code >= 100 && $code <= 599) ? $code : 400;
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message, 'code' => $httpCode]);
        exit;
    }
}
