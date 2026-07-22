<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AuditLog
{
    public function latestByRestaurant(int $restaurantId, ?string $action = null, int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));
        $sql = <<<SQL
            SELECT
                audit_logs.id,
                audit_logs.action,
                audit_logs.entity_type,
                audit_logs.entity_id,
                audit_logs.description,
                audit_logs.metadata,
                audit_logs.ip_address,
                audit_logs.created_at,
                users.name AS user_name,
                users.email AS user_email
            FROM audit_logs
            LEFT JOIN users
                ON users.id = audit_logs.user_id
               AND users.restaurant_id = audit_logs.restaurant_id
            WHERE audit_logs.restaurant_id = :restaurant_id
        SQL;
        $parameters = ['restaurant_id' => $restaurantId];

        if ($action !== null && $action !== '') {
            $sql .= ' AND audit_logs.action = :action';
            $parameters['action'] = $action;
        }

        $sql .= ' ORDER BY audit_logs.created_at DESC, audit_logs.id DESC LIMIT ' . $limit;
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);
        $logs = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($logs as &$log) {
            $decoded = json_decode((string) ($log['metadata'] ?? ''), true);
            $log['metadata_decoded'] = is_array($decoded) ? $decoded : [];
        }
        unset($log);

        return $logs;
    }

    public function actionsByRestaurant(int $restaurantId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT DISTINCT action
             FROM audit_logs
             WHERE restaurant_id = :restaurant_id
             ORDER BY action ASC'
        );
        $statement->execute(['restaurant_id' => $restaurantId]);

        return array_values(array_filter(array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN))));
    }
}
