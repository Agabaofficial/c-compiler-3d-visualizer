<?php

namespace App\Controllers;

use App\Services\VisualizationService;
use App\Middleware\AuthMiddleware;
use RuntimeException;

/**
 * VisualizationController – save, load, list, delete.
 */
class VisualizationController
{
    private VisualizationService $vizService;
    private AuthMiddleware       $authMiddleware;

    public function __construct()
    {
        $this->vizService     = new VisualizationService();
        $this->authMiddleware = new AuthMiddleware();
    }

    // ------------------------------------------------------------------
    // Endpoints
    // ------------------------------------------------------------------

    public function save(): void
    {
        $payload = $this->authMiddleware->requireAuth();
        $userId  = (int) ($payload['sub'] ?? 0);

        $body        = $this->parseJsonBody();
        $sessionId   = (int) ($body['session_id']  ?? 0);
        $name        = trim($body['name']           ?? '');
        $description = trim($body['description']    ?? '');
        $format      = trim($body['format']         ?? 'json');

        if ($sessionId === 0 || $name === '') {
            $this->error(400, 'session_id and name are required');
        }

        try {
            $result = $this->vizService->save($userId, $sessionId, $name, $description, $format);
            $this->success($result, 'Visualization saved', 201);
        } catch (RuntimeException $e) {
            $this->error($e->getCode() ?: 400, $e->getMessage());
        }
    }

    public function load(): void
    {
        $payload = $this->authMiddleware->requireAuth();
        $userId  = (int) ($payload['sub'] ?? 0);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id === 0) {
            $this->error(400, 'id is required');
        }

        try {
            $viz = $this->vizService->load($id, $userId);
            $this->success($viz);
        } catch (RuntimeException $e) {
            $this->error($e->getCode() ?: 404, $e->getMessage());
        }
    }

    public function list(): void
    {
        $payload = $this->authMiddleware->requireAuth();
        $userId  = (int) ($payload['sub'] ?? 0);

        $vizList = $this->vizService->list($userId);
        $this->success(['visualizations' => $vizList]);
    }

    public function delete(): void
    {
        $payload = $this->authMiddleware->requireAuth();
        $userId  = (int) ($payload['sub'] ?? 0);

        $body = $this->parseJsonBody();
        $id   = (int) ($body['id'] ?? $_GET['id'] ?? 0);

        if ($id === 0) {
            $this->error(400, 'id is required');
        }

        try {
            $this->vizService->delete($id, $userId);
            $this->success([], 'Visualization deleted');
        } catch (RuntimeException $e) {
            $this->error($e->getCode() ?: 400, $e->getMessage());
        }
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
