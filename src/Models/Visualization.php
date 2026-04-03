<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Visualization model.
 */
class Visualization
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Write operations
    // ------------------------------------------------------------------

    public function save(int $userId, int $sessionId, string $name, string $description, string $exportFormat): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO saved_visualizations (user_id, session_id, name, description, export_format)
             VALUES (:user_id, :session_id, :name, :description, :export_format)'
        );
        $stmt->execute([
            ':user_id'       => $userId,
            ':session_id'    => $sessionId,
            ':name'          => $name,
            ':description'   => $description,
            ':export_format' => $exportFormat,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'description', 'export_format', 'file_path'];
        $sets    = [];
        $params  = [':id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]              = "`{$field}` = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE saved_visualizations SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM saved_visualizations WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    // ------------------------------------------------------------------
    // Read operations
    // ------------------------------------------------------------------

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM saved_visualizations WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT sv.*, cs.language
             FROM saved_visualizations sv
             JOIN compilation_sessions cs ON sv.session_id = cs.id
             WHERE sv.user_id = :user_id
             ORDER BY sv.created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
