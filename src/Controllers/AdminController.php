<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\CompilationSession;
use App\Models\Analytics;
use App\Middleware\AuthMiddleware;
use App\Utils\Logger;

/**
 * AdminController – stats, users, logs.  All methods require admin role.
 */
class AdminController
{
    private User               $userModel;
    private CompilationSession $sessionModel;
    private Analytics          $analyticsModel;
    private AuthMiddleware     $authMiddleware;
    private Logger             $logger;

    public function __construct()
    {
        $this->userModel      = new User();
        $this->sessionModel   = new CompilationSession();
        $this->analyticsModel = new Analytics();
        $this->authMiddleware = new AuthMiddleware();
        $this->logger         = new Logger();
    }

    // ------------------------------------------------------------------
    // Endpoints
    // ------------------------------------------------------------------

    public function stats(): void
    {
        $this->authMiddleware->requireAdmin();

        $data = [
            'users'        => $this->userModel->count(),
            'compilations' => $this->sessionModel->count(),
            'session_stats'=> $this->sessionModel->getStats(),
            'system_stats' => $this->analyticsModel->getSystemStats(),
            'daily'        => $this->analyticsModel->getDailyStats(30),
        ];

        $this->success($data);
    }

    public function users(): void
    {
        $this->authMiddleware->requireAdmin();

        $page  = max(1, (int) ($_GET['page']  ?? 1));
        $limit = min(100, max(1, (int) ($_GET['limit'] ?? 50)));

        $users = $this->userModel->findAll($page, $limit);
        // Strip password hashes before returning
        foreach ($users as &$user) {
            unset($user['password_hash']);
        }
        unset($user);

        $this->success([
            'users' => $users,
            'total' => $this->userModel->count(),
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    public function deleteUser(int $id): void
    {
        $admin = $this->authMiddleware->requireAdmin();

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->error(404, 'User not found');
        }

        $this->userModel->delete($id);

        $this->logger->info('Admin deleted user', [
            'admin_id' => $admin['sub'],
            'user_id'  => $id,
        ]);

        $this->success([], 'User deleted');
    }

    public function updateUser(int $id): void
    {
        $admin = $this->authMiddleware->requireAdmin();

        $body    = $this->parseJsonBody();
        $allowed = ['role', 'is_banned', 'username', 'email'];
        $updates = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $updates[$field] = $body[$field];
            }
        }

        if (empty($updates)) {
            $this->error(400, 'No valid fields to update');
        }

        $this->userModel->update($id, $updates);

        $this->logger->info('Admin updated user', [
            'admin_id' => $admin['sub'],
            'user_id'  => $id,
            'fields'   => array_keys($updates),
        ]);

        $user = $this->userModel->findById($id);
        if ($user) {
            unset($user['password_hash']);
        }

        $this->success($user ?? [], 'User updated');
    }

    public function logs(): void
    {
        $this->authMiddleware->requireAdmin();

        $type  = $_GET['type'] ?? 'error';
        $lines = min(1000, max(1, (int) ($_GET['lines'] ?? 100)));

        $logFile = $type === 'access'
            ? (defined('ACCESS_LOG_FILE') ? ACCESS_LOG_FILE : APP_ROOT . '/logs/access.log')
            : (defined('LOG_FILE')        ? LOG_FILE        : APP_ROOT . '/logs/error.log');

        $entries = $this->readLogTail($logFile, $lines);

        $this->success([
            'type'    => $type,
            'file'    => basename($logFile),
            'entries' => $entries,
            'count'   => count($entries),
        ]);
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function readLogTail(string $path, int $lines): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $start    = max(0, $totalLines - $lines);
        $entries  = [];

        $file->seek($start);
        while (!$file->eof()) {
            $line = rtrim($file->current());
            if ($line !== '') {
                $entries[] = $line;
            }
            $file->next();
        }

        return array_reverse($entries);
    }

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
