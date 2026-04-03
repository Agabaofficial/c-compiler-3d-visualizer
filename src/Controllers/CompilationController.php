<?php

namespace App\Controllers;

use App\Services\CompilerService;
use App\Middleware\AuthMiddleware;
use RuntimeException;
use InvalidArgumentException;

/**
 * CompilationController – compile and history endpoints.
 */
class CompilationController
{
    private CompilerService $compilerService;
    private AuthMiddleware  $authMiddleware;

    public function __construct()
    {
        $this->compilerService = new CompilerService();
        $this->authMiddleware  = new AuthMiddleware();
    }

    // ------------------------------------------------------------------
    // Endpoints
    // ------------------------------------------------------------------

    public function compile(): void
    {
        // Optional auth – logged-in users get their history saved
        $this->authMiddleware->optionalAuth();
        $userId = isset($GLOBALS['user_id']) && $GLOBALS['user_id'] > 0 ? (int) $GLOBALS['user_id'] : null;

        $body     = $this->parseJsonBody();
        $language = trim($body['language'] ?? '');
        $code     = $body['code']           ?? '';

        if ($language === '' || $code === '') {
            $this->error(400, 'language and code are required');
        }

        try {
            $result = $this->compilerService->compile($language, $code, $userId);
            $this->success($result, 'Compilation complete');
        } catch (InvalidArgumentException $e) {
            $this->error(400, $e->getMessage());
        } catch (RuntimeException $e) {
            $this->error($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function history(): void
    {
        $user = $this->authMiddleware->requireAuth();
        $userId = (int) ($user['sub'] ?? 0);

        $page  = max(1, (int) ($_GET['page']  ?? 1));
        $limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));

        $history = $this->compilerService->getHistory($userId, $page, $limit);
        $this->success(['sessions' => $history, 'page' => $page, 'limit' => $limit]);
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
