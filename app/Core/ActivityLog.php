<?php

namespace App\Core;

class ActivityLog
{
    public static function ensureSchema(): void
    {
        Database::connection()->exec("
            CREATE TABLE IF NOT EXISTS system_activity_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(100) NOT NULL,
                entity_type VARCHAR(100) NULL,
                entity_id BIGINT NULL,
                user_id BIGINT NULL,
                actor_label VARCHAR(190) NULL,
                summary VARCHAR(255) NOT NULL,
                payload_json LONGTEXT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_system_activity_logs_event_type (event_type),
                INDEX idx_system_activity_logs_entity (entity_type, entity_id),
                INDEX idx_system_activity_logs_user_id (user_id),
                INDEX idx_system_activity_logs_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public static function record(string $eventType, string $summary, array $payload = [], ?string $entityType = null, ?int $entityId = null, ?int $userId = null, ?string $actorLabel = null): void
    {
        self::ensureSchema();

        Database::connection()->prepare('
            INSERT INTO system_activity_logs (event_type, entity_type, entity_id, user_id, actor_label, summary, payload_json, created_at)
            VALUES (:event_type, :entity_type, :entity_id, :user_id, :actor_label, :summary, :payload_json, NOW())
        ')->execute([
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $userId,
            'actor_label' => $actorLabel,
            'summary' => $summary,
            'payload_json' => $payload !== [] ? json_encode($payload, JSON_UNESCAPED_SLASHES) : null,
        ]);
    }

    public static function latest(int $limit = 50): array
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare('SELECT * FROM system_activity_logs ORDER BY created_at DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
