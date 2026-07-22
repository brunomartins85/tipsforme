<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public function findActiveByEmail(string $email): ?array
    {
        $sql = <<<SQL
            SELECT
                users.id,
                users.restaurant_id,
                users.name,
                users.email,
                users.password_hash,
                users.role,
                users.language,
                users.status,
                users.email_verified_at,
                restaurants.name AS restaurant_name,
                restaurants.slug AS restaurant_slug,
                restaurants.status AS restaurant_status,
                restaurants.timezone AS restaurant_timezone,
                restaurants.onboarding_completed_at,
                employees.id AS employee_id,
                employees.position AS employee_position
            FROM users
            INNER JOIN restaurants ON restaurants.id = users.restaurant_id
            LEFT JOIN employees
                ON employees.user_id = users.id
               AND employees.restaurant_id = users.restaurant_id
            WHERE users.email = :email
              AND users.status = 'active'
              AND users.email_verified_at IS NOT NULL
              AND restaurants.status = 'active'
            LIMIT 1
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['email' => text_lower(trim($email))]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                users.id,
                users.restaurant_id,
                users.name,
                users.email,
                users.role,
                users.language,
                users.status,
                users.email_verified_at,
                restaurants.name AS restaurant_name,
                restaurants.status AS restaurant_status,
                restaurants.password_reset_enabled
             FROM users
             INNER JOIN restaurants ON restaurants.id = users.restaurant_id
             WHERE users.email = :email
               AND users.status = \'active\'
               AND restaurants.status = \'active\'
             LIMIT 1'
        );
        $statement->execute(['email' => text_lower(trim($email))]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByIdForSettings(int $userId, int $restaurantId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, restaurant_id, name, email, password_hash, role, language, status
             FROM users
             WHERE id = :id
               AND restaurant_id = :restaurant_id
               AND role IN (\'admin\', \'manager\')
             LIMIT 1'
        );
        $statement->execute([
            'id' => $userId,
            'restaurant_id' => $restaurantId,
        ]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $parameters = ['email' => text_lower(trim($email))];

        if ($ignoreUserId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $parameters['ignore_id'] = $ignoreUserId;
        }

        $sql .= ' LIMIT 1';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);

        return (bool) $statement->fetchColumn();
    }

    public function updateProfile(int $userId, int $restaurantId, array $data): bool
    {
        $statement = Database::connection()->prepare(
            'UPDATE users
             SET name = :name,
                 email = :email,
                 language = :language
             WHERE id = :id
               AND restaurant_id = :restaurant_id
               AND role IN (\'admin\', \'manager\')'
        );

        return $statement->execute([
            'id' => $userId,
            'restaurant_id' => $restaurantId,
            'name' => $data['name'],
            'email' => text_lower(trim($data['email'])),
            'language' => $data['language'],
        ]);
    }

    public function updatePassword(int $userId, int $restaurantId, string $passwordHash): bool
    {
        $statement = Database::connection()->prepare(
            'UPDATE users
             SET password_hash = :password_hash
             WHERE id = :id
               AND restaurant_id = :restaurant_id
               AND role IN (\'admin\', \'manager\')'
        );

        return $statement->execute([
            'id' => $userId,
            'restaurant_id' => $restaurantId,
            'password_hash' => $passwordHash,
        ]);
    }

    public function createEmployeeUser(int $restaurantId, array $employee, string $passwordHash): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO users
                (restaurant_id, name, email, password_hash, role, language, status)
             VALUES
                (:restaurant_id, :name, :email, :password_hash, \'employee\', :language, :status)'
        );
        $statement->execute([
            'restaurant_id' => $restaurantId,
            'name' => $employee['name'],
            'email' => text_lower(trim((string) $employee['email'])),
            'password_hash' => $passwordHash,
            'language' => $employee['language'],
            'status' => $employee['status'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function syncEmployeeUser(int $userId, int $restaurantId, array $employee): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE users
             SET name = :name,
                 email = :email,
                 language = :language,
                 status = :status
             WHERE id = :id
               AND restaurant_id = :restaurant_id
               AND role = \'employee\''
        );
        $statement->execute([
            'id' => $userId,
            'restaurant_id' => $restaurantId,
            'name' => $employee['name'],
            'email' => text_lower(trim((string) $employee['email'])),
            'language' => $employee['language'],
            'status' => $employee['status'],
        ]);
    }

    public function updateLastLogin(int $userId, int $restaurantId): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE users SET last_login_at = NOW() WHERE id = :id AND restaurant_id = :restaurant_id'
        );

        $statement->execute([
            'id' => $userId,
            'restaurant_id' => $restaurantId,
        ]);
    }
}
