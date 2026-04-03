<?php

namespace App\Services;

use App\Models\Visualization;
use App\Models\CompilationSession;
use RuntimeException;

/**
 * VisualizationService – save, load, list, delete and export visualizations.
 */
class VisualizationService
{
    private Visualization     $vizModel;
    private CompilationSession $sessionModel;

    public function __construct()
    {
        $this->vizModel     = new Visualization();
        $this->sessionModel = new CompilationSession();
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

    /**
     * Persist a new visualization linked to a compilation session.
     *
     * @throws RuntimeException if the session does not belong to the user
     */
    public function save(int $userId, int $sessionId, string $name, string $description, string $format = 'json'): array
    {
        $session = $this->sessionModel->findById($sessionId);
        if (!$session) {
            throw new RuntimeException('Compilation session not found', 404);
        }
        if ($session['user_id'] !== null && (int) $session['user_id'] !== $userId) {
            throw new RuntimeException('Access denied to this session', 403);
        }

        $allowedFormats = ['json', 'xml', 'svg'];
        if (!in_array($format, $allowedFormats, true)) {
            $format = 'json';
        }

        $id = $this->vizModel->save($userId, $sessionId, $name, $description, $format);

        return [
            'id'          => $id,
            'user_id'     => $userId,
            'session_id'  => $sessionId,
            'name'        => $name,
            'description' => $description,
            'format'      => $format,
        ];
    }

    /**
     * Load a visualization and verify ownership.
     *
     * @throws RuntimeException on not-found or access-denied
     */
    public function load(int $id, int $userId): array
    {
        $viz = $this->vizModel->findById($id);
        if (!$viz) {
            throw new RuntimeException('Visualization not found', 404);
        }
        if ((int) $viz['user_id'] !== $userId) {
            throw new RuntimeException('Access denied', 403);
        }
        return $viz;
    }

    /**
     * Return all visualizations belonging to the user.
     */
    public function list(int $userId): array
    {
        return $this->vizModel->findByUser($userId);
    }

    /**
     * Delete a visualization after verifying ownership.
     *
     * @throws RuntimeException on not-found or access-denied
     */
    public function delete(int $id, int $userId): bool
    {
        $viz = $this->vizModel->findById($id);
        if (!$viz) {
            throw new RuntimeException('Visualization not found', 404);
        }
        if ((int) $viz['user_id'] !== $userId) {
            throw new RuntimeException('Access denied', 403);
        }
        return $this->vizModel->delete($id);
    }

    /**
     * Export the compilation result of a session in the requested format.
     *
     * @throws RuntimeException on invalid session
     */
    public function export(int $sessionId, string $format): string
    {
        $session = $this->sessionModel->findById($sessionId);
        if (!$session) {
            throw new RuntimeException('Session not found', 404);
        }

        $data = [
            'session_id' => $session['id'],
            'language'   => $session['language'],
            'status'     => $session['status'],
            'output'     => json_decode($session['compilation_output'] ?? '{}', true),
            'created_at' => $session['created_at'],
        ];

        switch (strtolower($format)) {
            case 'xml':
                return $this->toXml($data);
            case 'json':
            default:
                return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function toXml(array $data, string $root = 'visualization', int $depth = 0): string
    {
        $indent = str_repeat('  ', $depth);
        $xml    = ($depth === 0) ? "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" : '';
        $xml   .= "{$indent}<{$root}>\n";

        foreach ($data as $key => $value) {
            $tag = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $key);
            if (is_array($value)) {
                $xml .= $this->toXml($value, $tag, $depth + 1);
            } else {
                $escaped = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml    .= "{$indent}  <{$tag}>{$escaped}</{$tag}>\n";
            }
        }

        $xml .= "{$indent}</{$root}>\n";
        return $xml;
    }
}
