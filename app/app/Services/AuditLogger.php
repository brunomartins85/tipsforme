<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

final class AuditLogger
{
    public static function record(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        array $metadata = []
    ): void {
        $user = auth_user();

        if ($user === null) {
            return;
        }

        self::recordFor(
            (int) $user['restaurant_id'],
            (int) $user['id'],
            $action,
            $entityType,
            $entityId,
            $description,
            $metadata
        );
    }

    public static function recordFor(
        int $restaurantId,
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        array $metadata = []
    ): void {
        try {
            $statement = Database::connection()->prepare(
                'INSERT INTO audit_logs
                    (restaurant_id, user_id, action, entity_type, entity_id,
                     description, metadata, ip_address, user_agent, created_at)
                 VALUES
                    (:restaurant_id, :user_id, :action, :entity_type, :entity_id,
                     :description, :metadata, :ip_address, :user_agent, NOW())'
            );
            $statement->execute([
                'restaurant_id' => $restaurantId,
                'user_id' => $userId,
                'action' => substr($action, 0, 80),
                'entity_type' => $entityType !== null ? substr($entityType, 0, 80) : null,
                'entity_id' => $entityId,
                'description' => $description !== null ? substr($description, 0, 255) : null,
                'metadata' => $metadata !== []
                    ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : null,
                'ip_address' => client_ip(),
                'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]);
        } catch (Throwable $exception) {
            // Auditoria nunca deve interromper uma operação principal.
            error_log('Audit log error: ' . $exception->getMessage());
        }
    }
}
