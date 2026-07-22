<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final class PasswordResetService
{
    public function issue(int $restaurantId, int $userId, string $purpose): string
    {
        if (!in_array($purpose, ['activation', 'password_reset'], true)) {
            throw new RuntimeException('Invalid token purpose.');
        }

        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $delete = $connection->prepare(
                'DELETE FROM password_reset_tokens
                 WHERE user_id = :user_id
                   AND purpose = :purpose
                   AND used_at IS NULL'
            );
            $delete->execute([
                'user_id' => $userId,
                'purpose' => $purpose,
            ]);

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTimeImmutable('+60 minutes'))->format('Y-m-d H:i:s');

            $insert = $connection->prepare(
                'INSERT INTO password_reset_tokens
                    (restaurant_id, user_id, token_hash, purpose, expires_at)
                 VALUES
                    (:restaurant_id, :user_id, :token_hash, :purpose, :expires_at)'
            );
            $insert->execute([
                'restaurant_id' => $restaurantId,
                'user_id' => $userId,
                'token_hash' => $tokenHash,
                'purpose' => $purpose,
                'expires_at' => $expiresAt,
            ]);

            $connection->commit();

            return $token;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function findValid(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $statement = Database::connection()->prepare(
            'SELECT
                password_reset_tokens.id,
                password_reset_tokens.restaurant_id,
                password_reset_tokens.user_id,
                password_reset_tokens.purpose,
                password_reset_tokens.expires_at,
                users.name,
                users.email,
                users.language,
                users.status,
                restaurants.name AS restaurant_name,
                restaurants.status AS restaurant_status
             FROM password_reset_tokens
             INNER JOIN users ON users.id = password_reset_tokens.user_id
             INNER JOIN restaurants ON restaurants.id = password_reset_tokens.restaurant_id
             WHERE password_reset_tokens.token_hash = :token_hash
               AND password_reset_tokens.used_at IS NULL
               AND password_reset_tokens.expires_at > NOW()
               AND users.status = \'active\'
               AND restaurants.status = \'active\'
             LIMIT 1'
        );
        $statement->execute(['token_hash' => hash('sha256', $token)]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function consume(string $token, string $passwordHash): bool
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $record = $this->findValidForUpdate($connection, $token);

            if ($record === null) {
                $connection->rollBack();
                return false;
            }

            $updateUser = $connection->prepare(
                'UPDATE users
                 SET password_hash = :password_hash,
                     status = \'active\',
                     email_verified_at = COALESCE(email_verified_at, NOW())
                 WHERE id = :id
                   AND restaurant_id = :restaurant_id'
            );
            $updateUser->execute([
                'password_hash' => $passwordHash,
                'id' => $record['user_id'],
                'restaurant_id' => $record['restaurant_id'],
            ]);

            $markUsed = $connection->prepare(
                'UPDATE password_reset_tokens
                 SET used_at = NOW()
                 WHERE id = :id'
            );
            $markUsed->execute(['id' => $record['id']]);

            $invalidate = $connection->prepare(
                'UPDATE password_reset_tokens
                 SET used_at = COALESCE(used_at, NOW())
                 WHERE user_id = :user_id
                   AND id <> :id
                   AND used_at IS NULL'
            );
            $invalidate->execute([
                'user_id' => $record['user_id'],
                'id' => $record['id'],
            ]);

            $connection->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    private function findValidForUpdate(PDO $connection, string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $statement = $connection->prepare(
            'SELECT id, restaurant_id, user_id
             FROM password_reset_tokens
             WHERE token_hash = :token_hash
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1
             FOR UPDATE'
        );
        $statement->execute(['token_hash' => hash('sha256', $token)]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }
}
