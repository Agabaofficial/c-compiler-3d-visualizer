<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Analytics model.
 */
class Analytics
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Write operations
    // ------------------------------------------------------------------

    public function track(?int $userId, string $language, int $sessionDuration, array $featuresUsed, string $ipAddress): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO analytics (user_id, language_used, session_duration, features_used, ip_address)
             VALUES (:user_id, :language, :duration, :features, :ip)'
        );
        $stmt->execute([
            ':user_id'  => $userId,
            ':language' => $language,
            ':duration' => $sessionDuration,
            ':features' => json_encode($featuresUsed),
            ':ip'       => $ipAddress,
        ]);
        return (int) $this->db->lastInsertId();
    }

    // ------------------------------------------------------------------
    // Read / aggregation operations
    // ------------------------------------------------------------------

    /**
     * Total compilations per language across all users.
     */
    public function getLanguageStats(): array
    {
        return $this->db->query(
            'SELECT language_used AS language, COUNT(*) AS total,
                    ROUND(AVG(session_duration), 2) AS avg_duration
             FROM analytics
             GROUP BY language_used
             ORDER BY total DESC'
        )->fetchAll();
    }

    /**
     * Stats for a single user.
     */
    public function getUserStats(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT language_used, COUNT(*) AS total,
                    SUM(session_duration) AS total_duration,
                    MAX(created_at) AS last_activity
             FROM analytics
             WHERE user_id = :user_id
             GROUP BY language_used
             ORDER BY total DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * High-level system-wide statistics.
     */
    public function getSystemStats(): array
    {
        $totals = $this->db->query(
            'SELECT COUNT(*) AS total_sessions,
                    COUNT(DISTINCT user_id) AS unique_users,
                    ROUND(AVG(session_duration), 2) AS avg_duration,
                    SUM(session_duration) AS total_duration
             FROM analytics'
        )->fetch();

        return [
            'totals'          => $totals,
            'language_stats'  => $this->getLanguageStats(),
        ];
    }

    /**
     * Daily activity for the last $days days.
     */
    public function getDailyStats(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT DATE(created_at) AS day,
                    COUNT(*) AS sessions,
                    COUNT(DISTINCT user_id) AS unique_users
             FROM analytics
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC'
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
