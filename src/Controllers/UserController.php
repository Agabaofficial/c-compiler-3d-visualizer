<?php

namespace App\Controllers;

use App\Models\User;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;
use RuntimeException;

/**
 * UserController – profile, updateProfile, changePassword.
 */
class UserController
{
    private User           $userModel;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $this->userModel      = new User();
        $this->authMiddleware = new AuthMiddleware();
    }

    // ------------------------------------------------------------------
    // Endpoints
    // ------------------------------------------------------------------

    public function profile(): void
    {
        $payload = $this->authMiddleware->requireAuth();
        $userId  = (int) ($payload['sub'] ?? 0);

        $user = $this->userModel->findById($userId);
        if (!$user) {
            $this->error(404, 'User not found');
        }

        unset($user['password_hash']);
        $this->success($user);
    }

    public function updateProfile(): void
    {
        $payload = $this->authMiddleware->requireAuth();
        $userId  = (int) ($payload['sub'] ?? 0);

        $body     = $this->parseJsonBody();
        $allowed  = ['username', 'email'];
        $updates  = [];

        foreach ($allowed as $field) {
            if (isset($body[$field])) {
                $updates[$field] = trim($body[$field]);
            }
        }

        if (empty($updates)) {
            $this->error(400, 'No valid fields provided for update');
        }

        if (isset($updates['email']) && !Validator::validateEmail($updates['email'])) {
            $this->error(422, 'Invalid email address');
        }

        if (isset($updates['username']) && !Validator::validateUsername($updates['username'])) {
            $this->error(422, 'Username must be 3-50 alphanumeric characters or underscores');
        }

        $this->userModel->update($userId, $updates);

        $user = $this->userModel->findById($userId);
        unset($user['password_hash']);
        $this->success($user, 'Profile updated');
    }

    public function changePassword(): void
    {
        $payload = $this->authMiddleware->requireAuth();
        $userId  = (int) ($payload['sub'] ?? 0);

        $body        = $this->parseJsonBody();
        $currentPass = $body['current_password'] ?? '';
        $newPass     = $body['new_password']      ?? '';

        if ($currentPass === '' || $newPass === '') {
            $this->error(400, 'current_password and new_password are required');
        }

        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($currentPass, $user['password_hash'])) {
            $this->error(401, 'Current password is incorrect');
        }

        if (!Validator::validatePassword($newPass)) {
            $this->error(422, 'Password must be at least 8 characters with uppercase, lowercase, number and special character');
        }

        $this->userModel->updatePassword($userId, $newPass);
        $this->success([], 'Password changed successfully');
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
