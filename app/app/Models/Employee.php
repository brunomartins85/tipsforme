<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Employee
{
    public function allByRestaurant(int $restaurantId): array
    {
        $sql = <<<SQL
            SELECT
                employees.id,
                employees.user_id,
                employees.name,
                employees.email,
                employees.position,
                employees.language,
                employees.status,
                employees.created_at,
                users.last_login_at,
                users.status AS access_status,
                COUNT(DISTINCT shift_employees.shift_id) AS shift_count
            FROM employees
            LEFT JOIN users
                ON users.id = employees.user_id
               AND users.restaurant_id = employees.restaurant_id
            LEFT JOIN shift_employees
                ON shift_employees.employee_id = employees.id
               AND shift_employees.restaurant_id = employees.restaurant_id
            WHERE employees.restaurant_id = :restaurant_id
            GROUP BY
                employees.id,
                employees.user_id,
                employees.name,
                employees.email,
                employees.position,
                employees.language,
                employees.status,
                employees.created_at,
                users.last_login_at,
                users.status
            ORDER BY employees.status = 'active' DESC, employees.name ASC
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['restaurant_id' => $restaurantId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activeByRestaurant(int $restaurantId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, user_id, name, email, position, language, status
             FROM employees
             WHERE restaurant_id = :restaurant_id
               AND status = 'active'
             ORDER BY name ASC"
        );
        $statement->execute(['restaurant_id' => $restaurantId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $employeeId, int $restaurantId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, restaurant_id, user_id, name, email, position, language, status
             FROM employees
             WHERE id = :id AND restaurant_id = :restaurant_id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $employeeId,
            'restaurant_id' => $restaurantId,
        ]);
        $employee = $statement->fetch(PDO::FETCH_ASSOC);

        return $employee ?: null;
    }

    public function findByUserId(int $userId, int $restaurantId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, restaurant_id, user_id, name, email, position, language, status
             FROM employees
             WHERE user_id = :user_id
               AND restaurant_id = :restaurant_id
             LIMIT 1'
        );
        $statement->execute([
            'user_id' => $userId,
            'restaurant_id' => $restaurantId,
        ]);
        $employee = $statement->fetch(PDO::FETCH_ASSOC);

        return $employee ?: null;
    }

    public function emailExists(string $email, int $restaurantId, ?int $ignoreEmployeeId = null): bool
    {
        $sql = 'SELECT id FROM employees
                WHERE restaurant_id = :restaurant_id
                  AND email = :email';
        $parameters = [
            'restaurant_id' => $restaurantId,
            'email' => text_lower(trim($email)),
        ];

        if ($ignoreEmployeeId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $parameters['ignore_id'] = $ignoreEmployeeId;
        }

        $sql .= ' LIMIT 1';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);

        return (bool) $statement->fetchColumn();
    }

    public function create(int $restaurantId, array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO employees
                (restaurant_id, name, email, position, language, status)
             VALUES
                (:restaurant_id, :name, :email, :position, :language, :status)'
        );
        $statement->execute([
            'restaurant_id' => $restaurantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'position' => $data['position'],
            'language' => $data['language'],
            'status' => $data['status'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $employeeId, int $restaurantId, array $data): bool
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $statement = $connection->prepare(
                'UPDATE employees
                 SET name = :name,
                     email = :email,
                     position = :position,
                     language = :language
                 WHERE id = :id
                   AND restaurant_id = :restaurant_id'
            );

            $statement->execute([
                'id' => $employeeId,
                'restaurant_id' => $restaurantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'position' => $data['position'],
                'language' => $data['language'],
            ]);

            $employee = $this->findById($employeeId, $restaurantId);

            if ($employee !== null && !empty($employee['user_id']) && !empty($employee['email'])) {
                (new User())->syncEmployeeUser((int) $employee['user_id'], $restaurantId, $employee);
            }

            $connection->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function linkUser(int $employeeId, int $restaurantId, int $userId): bool
    {
        $statement = Database::connection()->prepare(
            'UPDATE employees
             SET user_id = :user_id
             WHERE id = :id
               AND restaurant_id = :restaurant_id
               AND user_id IS NULL'
        );
        $statement->execute([
            'user_id' => $userId,
            'id' => $employeeId,
            'restaurant_id' => $restaurantId,
        ]);

        return $statement->rowCount() === 1;
    }

    public function toggleStatus(int $employeeId, int $restaurantId): bool
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $employee = $this->findById($employeeId, $restaurantId);

            if ($employee === null) {
                $connection->rollBack();
                return false;
            }

            $newStatus = $employee['status'] === 'active' ? 'inactive' : 'active';
            $statement = $connection->prepare(
                'UPDATE employees
                 SET status = :status
                 WHERE id = :id
                   AND restaurant_id = :restaurant_id'
            );
            $statement->execute([
                'status' => $newStatus,
                'id' => $employeeId,
                'restaurant_id' => $restaurantId,
            ]);

            if (!empty($employee['user_id'])) {
                $userStatement = $connection->prepare(
                    'UPDATE users
                     SET status = :status
                     WHERE id = :id
                       AND restaurant_id = :restaurant_id
                       AND role = \'employee\''
                );
                $userStatement->execute([
                    'status' => $newStatus,
                    'id' => $employee['user_id'],
                    'restaurant_id' => $restaurantId,
                ]);
            }

            $connection->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function countActive(int $restaurantId): int
    {
        $statement = Database::connection()->prepare(
            "SELECT COUNT(*) FROM employees
             WHERE restaurant_id = :restaurant_id
               AND status = 'active'"
        );
        $statement->execute(['restaurant_id' => $restaurantId]);

        return (int) $statement->fetchColumn();
    }
}
