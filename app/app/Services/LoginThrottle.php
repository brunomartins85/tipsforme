<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

final class LoginThrottle
{
    public function isBlocked(string $email, string $ipAddress): bool
    {
        $maxAttempts = max(3, (int) env('LOGIN_MAX_ATTEMPTS', '5'));
        $lockMinutes = max(1, (int) env('LOGIN_LOCK_MINUTES', '15'));
        $since = date('Y-m-d H:i:s', time() - ($lockMinutes * 60));

        try {
            $statement = Database::connection()->prepare(
                'SELECT COUNT(*)
                 FROM login_attempts
                 WHERE email_hash = :email_hash
                   AND ip_address = :ip_address
                   AND success = 0
                   AND attempted_at >= :since'
            );
            $statement->execute([
                'email_hash' => $this->emailHash($email),
                'ip_address' => $ipAddress,
                'since' => $since,
            ]);

            return (int) $statement->fetchColumn() >= $maxAttempts;
        } catch (Throwable $exception) {
            error_log('Login throttle error: ' . $exception->getMessage());
            return false;
        }
    }

    public function record(string $email, string $ipAddress, bool $success): void
    {
        try {
            $statement = Database::connection()->prepare(
                'INSERT INTO login_attempts (email_hash, ip_address, success, attempted_at)
                 VALUES (:email_hash, :ip_address, :success, NOW())'
            );
        $statement->execute([
            'email_hash' => $this->emailHash($email),
            'ip_address' => $ipAddress,
            'success' => $success ? 1 : 0,
        ]);

        if ($success) {
            $delete = Database::connection()->prepare(
                'DELETE FROM login_attempts
                 WHERE email_hash = :email_hash
                   AND ip_address = :ip_address
                   AND success = 0'
            );
            $delete->execute([
                'email_hash' => $this->emailHash($email),
                'ip_address' => $ipAddress,
            ]);
        }

            if (random_int(1, 100) === 1) {
                Database::connection()->exec(
                    "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
                );
            }
        } catch (Throwable $exception) {
            error_log('Login throttle error: ' . $exception->getMessage());
        }
    }

    private function emailHash(string $email): string
    {
        return hash('sha256', text_lower(trim($email)));
    }
}
