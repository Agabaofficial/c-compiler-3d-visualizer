<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * CompilationSession model.
 */
class CompilationSession
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Write operations
    // ------------------------------------------------------------------

    /**
     * Insert a new pending session and return its id.
     */
    public function create(?int $userId, string $language, string $codeInput): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO compilation_sessions (user_id, language, code_input, status)
             VALUES (:user_id, :language, :code_input, "pending")'
        );
        $stmt->execute([
            ':user_id'    => $userId,
            ':language'   => $language,
            ':code_input' => $codeInput,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Write the compilation result back to the session row.
     */
    public function update(int $id, string $output, string $status, float $executionTime): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE compilation_sessions
             SET compilation_output = :output,
                 status             = :status,
                 execution_time     = :exec_time
             WHERE id = :id'
        );
        return $stmt->execute([
            ':output'    => $output,
            ':status'    => $status,
            ':exec_time' => $executionTime,
            ':id'        => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM compilation_sessions WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    // ------------------------------------------------------------------
    // Read operations
    // ------------------------------------------------------------------

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM compilation_sessions WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Return paginated sessions belonging to a user.
     */
    public function findByUser(int $userId, int $page = 1, int $limit = 20): array
    {
        $page   = max(1, $page);
        $limit  = min(100, max(1, $limit));
        $offset = ($page - 1) * $limit;

        $stmt = $this->db->prepare(
            'SELECT id, language, status, execution_time, created_at,
                    LEFT(code_input, 200) AS code_preview
             FROM compilation_sessions
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit',   $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset',  $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Aggregate compilation counts grouped by language and status.
     */
    public function getStats(): array
    {
        $byLanguage = $this->db->query(
            'SELECT language, COUNT(*) AS total
             FROM compilation_sessions
             GROUP BY language
             ORDER BY total DESC'
        )->fetchAll();

        $byStatus = $this->db->query(
            'SELECT status, COUNT(*) AS total
             FROM compilation_sessions
             GROUP BY status'
        )->fetchAll();

        return [
            'by_language' => $byLanguage,
            'by_status'   => $byStatus,
        ];
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM compilation_sessions')->fetchColumn();
    }
}
