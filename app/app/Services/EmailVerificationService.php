<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final class EmailVerificationService
{
    public function issue(int $restaurantId, int $userId): string
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $delete = $connection->prepare(
                'DELETE FROM email_verification_tokens
                 WHERE user_id = :user_id
                   AND used_at IS NULL'
            );
            $delete->execute(['user_id' => $userId]);

            $token = bin2hex(random_bytes(32));
            $expiresAt = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

            $insert = $connection->prepare(
                'INSERT INTO email_verification_tokens
                    (restaurant_id, user_id, token_hash, expires_at)
                 VALUES
                    (:restaurant_id, :user_id, :token_hash, :expires_at)'
            );
            $insert->execute([
                'restaurant_id' => $restaurantId,
                'user_id' => $userId,
                'token_hash' => hash('sha256', $token),
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

    public function verify(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $statement = $connection->prepare(
                'SELECT
                    email_verification_tokens.id,
                    email_verification_tokens.restaurant_id,
                    email_verification_tokens.user_id,
                    users.name,
                    users.email,
                    users.language,
                    restaurants.name AS restaurant_name
                 FROM email_verification_tokens
                 INNER JOIN users ON users.id = email_verification_tokens.user_id
                 INNER JOIN restaurants ON restaurants.id = email_verification_tokens.restaurant_id
                 WHERE email_verification_tokens.token_hash = :token_hash
                   AND email_verification_tokens.used_at IS NULL
                   AND email_verification_tokens.expires_at > NOW()
                   AND restaurants.status = \'pending_verification\'
                 LIMIT 1
                 FOR UPDATE'
            );
            $statement->execute(['token_hash' => hash('sha256', $token)]);
            $record = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $connection->rollBack();
                return null;
            }

            $updateUser = $connection->prepare(
                'UPDATE users
                 SET email_verified_at = NOW()
                 WHERE id = :user_id
                   AND restaurant_id = :restaurant_id'
            );
            $updateUser->execute([
                'user_id' => $record['user_id'],
                'restaurant_id' => $record['restaurant_id'],
            ]);

            $updateRestaurant = $connection->prepare(
                'UPDATE restaurants
                 SET status = \'active\'
                 WHERE id = :restaurant_id
                   AND status = \'pending_verification\''
            );
            $updateRestaurant->execute(['restaurant_id' => $record['restaurant_id']]);

            $markUsed = $connection->prepare(
                'UPDATE email_verification_tokens
                 SET used_at = NOW()
                 WHERE id = :id'
            );
            $markUsed->execute(['id' => $record['id']]);

            $invalidate = $connection->prepare(
                'UPDATE email_verification_tokens
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
            return $record;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function findPendingByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                users.id AS user_id,
                users.restaurant_id,
                users.name,
                users.email,
                users.language,
                restaurants.name AS restaurant_name
             FROM users
             INNER JOIN restaurants ON restaurants.id = users.restaurant_id
             WHERE users.email = :email
               AND users.status = \'active\'
               AND users.email_verified_at IS NULL
               AND restaurants.status = \'pending_verification\'
             LIMIT 1'
        );
        $statement->execute(['email' => text_lower(trim($email))]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }
}
