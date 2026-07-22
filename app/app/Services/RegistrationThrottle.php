<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class RegistrationThrottle
{
    public function isBlocked(string $ipAddress): bool
    {
        $maxAttempts = max(1, (int) env('REGISTRATION_MAX_ATTEMPTS', 5));
        $windowMinutes = max(5, (int) env('REGISTRATION_WINDOW_MINUTES', 60));

        $since = date('Y-m-d H:i:s', time() - ($windowMinutes * 60));
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM registration_attempts
             WHERE ip_address = :ip_address
               AND attempted_at >= :since'
        );
        $statement->execute([
            'ip_address' => $ipAddress,
            'since' => $since,
        ]);

        return (int) $statement->fetchColumn() >= $maxAttempts;
    }

    public function record(string $ipAddress, bool $success): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO registration_attempts (ip_address, success, attempted_at)
             VALUES (:ip_address, :success, NOW())'
        );
        $statement->execute([
            'ip_address' => $ipAddress,
            'success' => $success ? 1 : 0,
        ]);

        // Limpeza simples para evitar crescimento ilimitado.
        Database::connection()->exec(
            'DELETE FROM registration_attempts
             WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)'
        );
    }
}
