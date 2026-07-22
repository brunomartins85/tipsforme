<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

final class Shift
{
    public function allByRestaurant(int $restaurantId): array
    {
        $sql = <<<SQL
            SELECT
                shifts.id,
                shifts.shift_date,
                shifts.shift_type,
                shifts.status,
                shifts.notes,
                shifts.created_at,
                tip_entries.id AS tip_entry_id,
                COUNT(shift_employees.employee_id) AS employee_count,
                GROUP_CONCAT(employees.name ORDER BY employees.name SEPARATOR ', ') AS employee_names
            FROM shifts
            LEFT JOIN shift_employees
                ON shift_employees.shift_id = shifts.id
               AND shift_employees.restaurant_id = shifts.restaurant_id
            LEFT JOIN employees
                ON employees.id = shift_employees.employee_id
               AND employees.restaurant_id = shifts.restaurant_id
            LEFT JOIN tip_entries
                ON tip_entries.shift_id = shifts.id
               AND tip_entries.restaurant_id = shifts.restaurant_id
            WHERE shifts.restaurant_id = :restaurant_id
            GROUP BY
                shifts.id,
                shifts.shift_date,
                shifts.shift_type,
                shifts.status,
                shifts.notes,
                shifts.created_at,
                tip_entries.id
            ORDER BY shifts.shift_date DESC, shifts.shift_type DESC, shifts.id DESC
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['restaurant_id' => $restaurantId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentByRestaurant(int $restaurantId, int $limit = 5): array
    {
        $sql = <<<SQL
            SELECT
                shifts.id,
                shifts.shift_date,
                shifts.shift_type,
                shifts.status,
                COUNT(shift_employees.employee_id) AS employee_count
            FROM shifts
            LEFT JOIN shift_employees
                ON shift_employees.shift_id = shifts.id
               AND shift_employees.restaurant_id = shifts.restaurant_id
            WHERE shifts.restaurant_id = :restaurant_id
            GROUP BY shifts.id, shifts.shift_date, shifts.shift_type, shifts.status
            ORDER BY shifts.shift_date DESC, shifts.shift_type DESC, shifts.id DESC
            LIMIT :limit
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->bindValue(':restaurant_id', $restaurantId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $shiftId, int $restaurantId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, restaurant_id, shift_date, shift_type, status, notes
             FROM shifts
             WHERE id = :id AND restaurant_id = :restaurant_id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $shiftId,
            'restaurant_id' => $restaurantId,
        ]);
        $shift = $statement->fetch(PDO::FETCH_ASSOC);

        return $shift ?: null;
    }

    public function employeeIds(int $shiftId, int $restaurantId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT employee_id
             FROM shift_employees
             WHERE shift_id = :shift_id
               AND restaurant_id = :restaurant_id'
        );
        $statement->execute([
            'shift_id' => $shiftId,
            'restaurant_id' => $restaurantId,
        ]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public function existsForDateAndType(
        int $restaurantId,
        string $shiftDate,
        string $shiftType,
        ?int $ignoreShiftId = null
    ): bool {
        $sql = 'SELECT id FROM shifts
                WHERE restaurant_id = :restaurant_id
                  AND shift_date = :shift_date
                  AND shift_type = :shift_type';
        $parameters = [
            'restaurant_id' => $restaurantId,
            'shift_date' => $shiftDate,
            'shift_type' => $shiftType,
        ];

        if ($ignoreShiftId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $parameters['ignore_id'] = $ignoreShiftId;
        }

        $sql .= ' LIMIT 1';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);

        return (bool) $statement->fetchColumn();
    }

    public function create(int $restaurantId, int $userId, array $data, array $employeeIds): int
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $statement = $connection->prepare(
                'INSERT INTO shifts
                    (restaurant_id, shift_date, shift_type, status, notes, created_by, updated_by)
                 VALUES
                    (:restaurant_id, :shift_date, :shift_type, :status, :notes, :created_by, :updated_by)'
            );
            $statement->execute([
                'restaurant_id' => $restaurantId,
                'shift_date' => $data['shift_date'],
                'shift_type' => $data['shift_type'],
                'status' => 'open',
                'notes' => $data['notes'],
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $shiftId = (int) $connection->lastInsertId();
            $this->syncEmployees($shiftId, $restaurantId, $employeeIds);
            $connection->commit();

            return $shiftId;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function update(
        int $shiftId,
        int $restaurantId,
        int $userId,
        array $data,
        array $employeeIds
    ): bool {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $statement = $connection->prepare(
                'UPDATE shifts
                 SET shift_date = :shift_date,
                     shift_type = :shift_type,
                     notes = :notes,
                     updated_by = :updated_by
                 WHERE id = :id
                   AND restaurant_id = :restaurant_id
                   AND status = \'open\''
            );
            $statement->execute([
                'id' => $shiftId,
                'restaurant_id' => $restaurantId,
                'shift_date' => $data['shift_date'],
                'shift_type' => $data['shift_type'],
                'notes' => $data['notes'],
                'updated_by' => $userId,
            ]);

            $this->syncEmployees($shiftId, $restaurantId, $employeeIds);
            $connection->commit();

            return true;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function delete(int $shiftId, int $restaurantId): bool
    {
        $statement = Database::connection()->prepare(
            "DELETE FROM shifts
             WHERE id = :id
               AND restaurant_id = :restaurant_id
               AND status = 'open'"
        );
        $statement->execute([
            'id' => $shiftId,
            'restaurant_id' => $restaurantId,
        ]);

        return $statement->rowCount() === 1;
    }

    public function countCurrentMonth(int $restaurantId): int
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM shifts
             WHERE restaurant_id = :restaurant_id
               AND YEAR(shift_date) = YEAR(CURRENT_DATE())
               AND MONTH(shift_date) = MONTH(CURRENT_DATE())'
        );
        $statement->execute(['restaurant_id' => $restaurantId]);

        return (int) $statement->fetchColumn();
    }

    private function syncEmployees(int $shiftId, int $restaurantId, array $employeeIds): void
    {
        $connection = Database::connection();
        $delete = $connection->prepare(
            'DELETE FROM shift_employees
             WHERE shift_id = :shift_id
               AND restaurant_id = :restaurant_id'
        );
        $delete->execute([
            'shift_id' => $shiftId,
            'restaurant_id' => $restaurantId,
        ]);

        $insert = $connection->prepare(
            'INSERT INTO shift_employees (shift_id, employee_id, restaurant_id)
             VALUES (:shift_id, :employee_id, :restaurant_id)'
        );

        foreach (array_unique($employeeIds) as $employeeId) {
            $insert->execute([
                'shift_id' => $shiftId,
                'employee_id' => $employeeId,
                'restaurant_id' => $restaurantId,
            ]);
        }
    }
}
