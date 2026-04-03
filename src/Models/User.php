<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * User model – all queries use prepared statements.
 */
class User
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
     * Create a new user and return the inserted row's id.
     * Password is hashed with bcrypt.
     */
    public function create(string $username, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash)
             VALUES (:username, :email, :password_hash)'
        );
        $stmt->execute([
            ':username'      => $username,
            ':email'         => strtolower(trim($email)),
            ':password_hash' => $hash,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update mutable fields for a user.
     * Only the keys present in $data are updated.
     * Allowed fields: username, email, role, is_banned
     */
    public function update(int $id, array $data): bool
    {
        $allowed = ['username', 'email', 'role', 'is_banned'];
        $sets    = [];
        $params  = [':id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]           = "`{$field}` = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql  = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateLastLogin(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        return $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    public function ban(int $id, bool $banned = true): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_banned = :banned WHERE id = :id');
        return $stmt->execute([':banned' => (int) $banned, ':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    // ------------------------------------------------------------------
    // Read operations
    // ------------------------------------------------------------------

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Return a paginated list of all users.
     */
    public function findAll(int $page = 1, int $limit = 50): array
    {
        $page   = max(1, $page);
        $limit  = min(100, max(1, $limit));
        $offset = ($page - 1) * $limit;

        $stmt = $this->db->prepare(
            'SELECT id, username, email, role, is_banned, created_at, last_login
             FROM users
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
