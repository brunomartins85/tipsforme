<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Support\Money;
use PDO;
use RuntimeException;
use Throwable;

final class TipEntry
{
    public function allByRestaurant(int $restaurantId): array
    {
        $sql = <<<SQL
            SELECT
                tip_entries.id,
                tip_entries.shift_id,
                tip_entries.cash_amount,
                tip_entries.card_gross_amount,
                tip_entries.card_fee_percentage,
                tip_entries.card_fee_amount,
                tip_entries.card_net_amount,
                tip_entries.total_net_amount,
                tip_entries.status,
                tip_entries.created_at,
                shifts.shift_date,
                shifts.shift_type,
                COUNT(tip_distributions.id) AS employee_count
            FROM tip_entries
            INNER JOIN shifts
                ON shifts.id = tip_entries.shift_id
               AND shifts.restaurant_id = tip_entries.restaurant_id
            LEFT JOIN tip_distributions
                ON tip_distributions.tip_entry_id = tip_entries.id
               AND tip_distributions.restaurant_id = tip_entries.restaurant_id
            WHERE tip_entries.restaurant_id = :restaurant_id
            GROUP BY
                tip_entries.id,
                tip_entries.shift_id,
                tip_entries.cash_amount,
                tip_entries.card_gross_amount,
                tip_entries.card_fee_percentage,
                tip_entries.card_fee_amount,
                tip_entries.card_net_amount,
                tip_entries.total_net_amount,
                tip_entries.status,
                tip_entries.created_at,
                shifts.shift_date,
                shifts.shift_type
            ORDER BY shifts.shift_date DESC, shifts.shift_type DESC, tip_entries.id DESC
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['restaurant_id' => $restaurantId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function availableShifts(int $restaurantId): array
    {
        $sql = <<<SQL
            SELECT
                shifts.id,
                shifts.shift_date,
                shifts.shift_type,
                shifts.notes,
                COUNT(shift_employees.employee_id) AS employee_count,
                GROUP_CONCAT(employees.name ORDER BY employees.name SEPARATOR ', ') AS employee_names
            FROM shifts
            INNER JOIN shift_employees
                ON shift_employees.shift_id = shifts.id
               AND shift_employees.restaurant_id = shifts.restaurant_id
            INNER JOIN employees
                ON employees.id = shift_employees.employee_id
               AND employees.restaurant_id = shifts.restaurant_id
            LEFT JOIN tip_entries
                ON tip_entries.shift_id = shifts.id
               AND tip_entries.restaurant_id = shifts.restaurant_id
            WHERE shifts.restaurant_id = :restaurant_id
              AND shifts.status = 'open'
              AND tip_entries.id IS NULL
            GROUP BY shifts.id, shifts.shift_date, shifts.shift_type, shifts.notes
            ORDER BY shifts.shift_date DESC, shifts.shift_type DESC
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['restaurant_id' => $restaurantId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAvailableShift(int $shiftId, int $restaurantId): ?array
    {
        $sql = <<<SQL
            SELECT
                shifts.id,
                shifts.shift_date,
                shifts.shift_type,
                shifts.notes,
                COUNT(shift_employees.employee_id) AS employee_count,
                GROUP_CONCAT(employees.name ORDER BY employees.name SEPARATOR ', ') AS employee_names
            FROM shifts
            INNER JOIN shift_employees
                ON shift_employees.shift_id = shifts.id
               AND shift_employees.restaurant_id = shifts.restaurant_id
            INNER JOIN employees
                ON employees.id = shift_employees.employee_id
               AND employees.restaurant_id = shifts.restaurant_id
            LEFT JOIN tip_entries
                ON tip_entries.shift_id = shifts.id
               AND tip_entries.restaurant_id = shifts.restaurant_id
            WHERE shifts.id = :shift_id
              AND shifts.restaurant_id = :restaurant_id
              AND shifts.status = 'open'
              AND tip_entries.id IS NULL
            GROUP BY shifts.id, shifts.shift_date, shifts.shift_type, shifts.notes
            LIMIT 1
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute([
            'shift_id' => $shiftId,
            'restaurant_id' => $restaurantId,
        ]);
        $shift = $statement->fetch(PDO::FETCH_ASSOC);

        return $shift ?: null;
    }

    public function findById(int $entryId, int $restaurantId): ?array
    {
        $sql = <<<SQL
            SELECT
                tip_entries.*,
                shifts.shift_date,
                shifts.shift_type,
                shifts.notes AS shift_notes,
                (
                    SELECT COUNT(*)
                    FROM tip_distributions distribution_count
                    WHERE distribution_count.tip_entry_id = tip_entries.id
                      AND distribution_count.restaurant_id = tip_entries.restaurant_id
                ) AS employee_count,
                (
                    SELECT GROUP_CONCAT(employee_names.name ORDER BY employee_names.name SEPARATOR ', ')
                    FROM tip_distributions distribution_names
                    INNER JOIN employees employee_names
                        ON employee_names.id = distribution_names.employee_id
                       AND employee_names.restaurant_id = distribution_names.restaurant_id
                    WHERE distribution_names.tip_entry_id = tip_entries.id
                      AND distribution_names.restaurant_id = tip_entries.restaurant_id
                ) AS employee_names
            FROM tip_entries
            INNER JOIN shifts
                ON shifts.id = tip_entries.shift_id
               AND shifts.restaurant_id = tip_entries.restaurant_id
            WHERE tip_entries.id = :id
              AND tip_entries.restaurant_id = :restaurant_id
            LIMIT 1
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute([
            'id' => $entryId,
            'restaurant_id' => $restaurantId,
        ]);
        $entry = $statement->fetch(PDO::FETCH_ASSOC);

        return $entry ?: null;
    }

    public function distributions(int $entryId, int $restaurantId): array
    {
        $sql = <<<SQL
            SELECT
                tip_distributions.employee_id,
                employees.name,
                employees.position,
                tip_distributions.cash_amount,
                tip_distributions.card_gross_amount,
                tip_distributions.card_fee_amount,
                tip_distributions.card_net_amount,
                tip_distributions.total_amount
            FROM tip_distributions
            INNER JOIN employees
                ON employees.id = tip_distributions.employee_id
               AND employees.restaurant_id = tip_distributions.restaurant_id
            WHERE tip_distributions.tip_entry_id = :entry_id
              AND tip_distributions.restaurant_id = :restaurant_id
            ORDER BY employees.name ASC
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute([
            'entry_id' => $entryId,
            'restaurant_id' => $restaurantId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(int $restaurantId, int $userId, array $data): int
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $shift = $this->lockShift($connection, (int) $data['shift_id'], $restaurantId);

            if ($shift === null || $shift['status'] !== 'open') {
                throw new RuntimeException('Shift is not available.');
            }

            if ($this->entryExistsForShift($connection, (int) $data['shift_id'], $restaurantId)) {
                throw new RuntimeException('A tip entry already exists for this shift.');
            }

            $employeeIds = $this->shiftEmployeeIds($connection, (int) $data['shift_id'], $restaurantId);

            if ($employeeIds === []) {
                throw new RuntimeException('The shift has no employees.');
            }

            $calculation = $this->calculate($data, $employeeIds);
            $statement = $connection->prepare(
                'INSERT INTO tip_entries
                    (restaurant_id, shift_id, cash_amount, card_gross_amount,
                     card_fee_percentage, card_fee_amount, card_net_amount,
                     total_net_amount, status, notes, created_by, updated_by)
                 VALUES
                    (:restaurant_id, :shift_id, :cash_amount, :card_gross_amount,
                     :card_fee_percentage, :card_fee_amount, :card_net_amount,
                     :total_net_amount, :status, :notes, :created_by, :updated_by)'
            );
            $statement->execute([
                'restaurant_id' => $restaurantId,
                'shift_id' => $data['shift_id'],
                'cash_amount' => Money::toDatabase($data['cash_cents']),
                'card_gross_amount' => Money::toDatabase($data['card_gross_cents']),
                'card_fee_percentage' => number_format((float) $data['fee_percentage'], 2, '.', ''),
                'card_fee_amount' => Money::toDatabase($calculation['card_fee_cents']),
                'card_net_amount' => Money::toDatabase($calculation['card_net_cents']),
                'total_net_amount' => Money::toDatabase($calculation['total_net_cents']),
                'status' => 'open',
                'notes' => $data['notes'],
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $entryId = (int) $connection->lastInsertId();
            $this->insertDistributions(
                $connection,
                $entryId,
                (int) $data['shift_id'],
                $restaurantId,
                $calculation['distributions']
            );

            $updateShift = $connection->prepare(
                "UPDATE shifts
                 SET status = 'closed', updated_by = :updated_by
                 WHERE id = :shift_id AND restaurant_id = :restaurant_id"
            );
            $updateShift->execute([
                'updated_by' => $userId,
                'shift_id' => $data['shift_id'],
                'restaurant_id' => $restaurantId,
            ]);

            $connection->commit();

            return $entryId;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function update(int $entryId, int $restaurantId, int $userId, array $data): bool
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $entry = $this->lockEntry($connection, $entryId, $restaurantId);

            if ($entry === null || $entry['status'] !== 'open') {
                throw new RuntimeException('Tip entry cannot be changed.');
            }

            $employeeIds = $this->shiftEmployeeIds($connection, (int) $entry['shift_id'], $restaurantId);

            if ($employeeIds === []) {
                throw new RuntimeException('The shift has no employees.');
            }

            $data['shift_id'] = (int) $entry['shift_id'];
            $calculation = $this->calculate($data, $employeeIds);
            $statement = $connection->prepare(
                'UPDATE tip_entries
                 SET cash_amount = :cash_amount,
                     card_gross_amount = :card_gross_amount,
                     card_fee_percentage = :card_fee_percentage,
                     card_fee_amount = :card_fee_amount,
                     card_net_amount = :card_net_amount,
                     total_net_amount = :total_net_amount,
                     notes = :notes,
                     updated_by = :updated_by
                 WHERE id = :id
                   AND restaurant_id = :restaurant_id
                   AND status = \'open\''
            );
            $statement->execute([
                'id' => $entryId,
                'restaurant_id' => $restaurantId,
                'cash_amount' => Money::toDatabase($data['cash_cents']),
                'card_gross_amount' => Money::toDatabase($data['card_gross_cents']),
                'card_fee_percentage' => number_format((float) $data['fee_percentage'], 2, '.', ''),
                'card_fee_amount' => Money::toDatabase($calculation['card_fee_cents']),
                'card_net_amount' => Money::toDatabase($calculation['card_net_cents']),
                'total_net_amount' => Money::toDatabase($calculation['total_net_cents']),
                'notes' => $data['notes'],
                'updated_by' => $userId,
            ]);

            $delete = $connection->prepare(
                'DELETE FROM tip_distributions
                 WHERE tip_entry_id = :entry_id
                   AND restaurant_id = :restaurant_id'
            );
            $delete->execute([
                'entry_id' => $entryId,
                'restaurant_id' => $restaurantId,
            ]);

            $this->insertDistributions(
                $connection,
                $entryId,
                (int) $entry['shift_id'],
                $restaurantId,
                $calculation['distributions']
            );

            $connection->commit();

            return true;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function delete(int $entryId, int $restaurantId, int $userId): bool
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $entry = $this->lockEntry($connection, $entryId, $restaurantId);

            if ($entry === null || $entry['status'] !== 'open') {
                $connection->rollBack();
                return false;
            }

            $delete = $connection->prepare(
                "DELETE FROM tip_entries
                 WHERE id = :id
                   AND restaurant_id = :restaurant_id
                   AND status = 'open'"
            );
            $delete->execute([
                'id' => $entryId,
                'restaurant_id' => $restaurantId,
            ]);

            $updateShift = $connection->prepare(
                "UPDATE shifts
                 SET status = 'open', updated_by = :updated_by
                 WHERE id = :shift_id AND restaurant_id = :restaurant_id"
            );
            $updateShift->execute([
                'updated_by' => $userId,
                'shift_id' => $entry['shift_id'],
                'restaurant_id' => $restaurantId,
            ]);

            $connection->commit();

            return true;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function currentMonthTotals(int $restaurantId): array
    {
        $period = $this->currentMonthPeriod();
        $statement = Database::connection()->prepare(
            'SELECT
                COALESCE(SUM(cash_amount), 0) AS cash_total,
                COALESCE(SUM(card_gross_amount), 0) AS card_gross_total,
                COALESCE(SUM(card_fee_amount), 0) AS card_fee_total,
                COALESCE(SUM(card_net_amount), 0) AS card_net_total,
                COALESCE(SUM(total_net_amount), 0) AS net_total,
                COUNT(*) AS entry_count
             FROM tip_entries
             INNER JOIN shifts
                ON shifts.id = tip_entries.shift_id
               AND shifts.restaurant_id = tip_entries.restaurant_id
             WHERE tip_entries.restaurant_id = :restaurant_id
               AND shifts.shift_date >= :month_start
               AND shifts.shift_date < :next_month_start'
        );
        $statement->execute([
            'restaurant_id' => $restaurantId,
            'month_start' => $period['month_start'],
            'next_month_start' => $period['next_month_start'],
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function currentMonthPendingTotals(int $restaurantId): array
    {
        $period = $this->currentMonthPeriod();
        $statement = Database::connection()->prepare(
            'SELECT
                COALESCE(SUM(
                    CASE WHEN tip_distributions.cash_settlement_id IS NULL
                        THEN tip_distributions.cash_amount ELSE 0 END
                ), 0) AS cash_pending,
                COALESCE(SUM(
                    CASE WHEN tip_distributions.card_settlement_id IS NULL
                        THEN tip_distributions.card_net_amount ELSE 0 END
                ), 0) AS card_net_pending,
                COALESCE(SUM(
                    CASE WHEN tip_distributions.cash_settlement_id IS NULL
                        THEN tip_distributions.cash_amount ELSE 0 END
                    +
                    CASE WHEN tip_distributions.card_settlement_id IS NULL
                        THEN tip_distributions.card_net_amount ELSE 0 END
                ), 0) AS pending_total,
                COALESCE(SUM(
                    CASE WHEN tip_distributions.cash_settlement_id IS NOT NULL
                        THEN tip_distributions.cash_amount ELSE 0 END
                    +
                    CASE WHEN tip_distributions.card_settlement_id IS NOT NULL
                        THEN tip_distributions.card_net_amount ELSE 0 END
                ), 0) AS paid_total,
                COUNT(DISTINCT tip_distributions.tip_entry_id) AS entry_count
             FROM tip_distributions
             INNER JOIN shifts
                ON shifts.id = tip_distributions.shift_id
               AND shifts.restaurant_id = tip_distributions.restaurant_id
             WHERE tip_distributions.restaurant_id = :restaurant_id
               AND shifts.shift_date >= :month_start
               AND shifts.shift_date < :next_month_start'
        );
        $statement->execute([
            'restaurant_id' => $restaurantId,
            'month_start' => $period['month_start'],
            'next_month_start' => $period['next_month_start'],
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function recentByRestaurant(int $restaurantId, int $limit = 5): array
    {
        $sql = <<<SQL
            SELECT
                tip_entries.id,
                tip_entries.cash_amount,
                tip_entries.card_net_amount,
                tip_entries.total_net_amount,
                tip_entries.status,
                shifts.shift_date,
                shifts.shift_type,
                COUNT(tip_distributions.id) AS employee_count
            FROM tip_entries
            INNER JOIN shifts
                ON shifts.id = tip_entries.shift_id
               AND shifts.restaurant_id = tip_entries.restaurant_id
            LEFT JOIN tip_distributions
                ON tip_distributions.tip_entry_id = tip_entries.id
               AND tip_distributions.restaurant_id = tip_entries.restaurant_id
            WHERE tip_entries.restaurant_id = :restaurant_id
            GROUP BY
                tip_entries.id,
                tip_entries.cash_amount,
                tip_entries.card_net_amount,
                tip_entries.total_net_amount,
                tip_entries.status,
                shifts.shift_date,
                shifts.shift_type
            ORDER BY shifts.shift_date DESC, shifts.shift_type DESC, tip_entries.id DESC
            LIMIT :limit
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->bindValue(':restaurant_id', $restaurantId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }


    private function currentMonthPeriod(): array
    {
        $monthStart = new \DateTimeImmutable('first day of this month');

        return [
            'month_start' => $monthStart->format('Y-m-d'),
            'next_month_start' => $monthStart->modify('first day of next month')->format('Y-m-d'),
        ];
    }


    private function calculate(array $data, array $employeeIds): array
    {
        $cardFeeCents = Money::feeInCents($data['card_gross_cents'], $data['fee_percentage']);
        $cardNetCents = $data['card_gross_cents'] - $cardFeeCents;
        $totalNetCents = $data['cash_cents'] + $cardNetCents;
        $cashShares = Money::splitCents($data['cash_cents'], $employeeIds);
        $cardGrossShares = Money::splitCents($data['card_gross_cents'], $employeeIds);
        $cardFeeShares = Money::splitCents($cardFeeCents, $employeeIds);
        $distributions = [];

        foreach ($employeeIds as $employeeId) {
            $employeeId = (int) $employeeId;
            $cash = $cashShares[$employeeId];
            $cardGross = $cardGrossShares[$employeeId];
            $cardFee = $cardFeeShares[$employeeId];
            $cardNet = $cardGross - $cardFee;

            $distributions[$employeeId] = [
                'cash_cents' => $cash,
                'card_gross_cents' => $cardGross,
                'card_fee_cents' => $cardFee,
                'card_net_cents' => $cardNet,
                'total_cents' => $cash + $cardNet,
            ];
        }

        return [
            'card_fee_cents' => $cardFeeCents,
            'card_net_cents' => $cardNetCents,
            'total_net_cents' => $totalNetCents,
            'distributions' => $distributions,
        ];
    }

    private function insertDistributions(
        PDO $connection,
        int $entryId,
        int $shiftId,
        int $restaurantId,
        array $distributions
    ): void {
        $statement = $connection->prepare(
            'INSERT INTO tip_distributions
                (restaurant_id, tip_entry_id, shift_id, employee_id, cash_amount,
                 card_gross_amount, card_fee_amount, card_net_amount, total_amount)
             VALUES
                (:restaurant_id, :tip_entry_id, :shift_id, :employee_id, :cash_amount,
                 :card_gross_amount, :card_fee_amount, :card_net_amount, :total_amount)'
        );

        foreach ($distributions as $employeeId => $distribution) {
            $statement->execute([
                'restaurant_id' => $restaurantId,
                'tip_entry_id' => $entryId,
                'shift_id' => $shiftId,
                'employee_id' => $employeeId,
                'cash_amount' => Money::toDatabase($distribution['cash_cents']),
                'card_gross_amount' => Money::toDatabase($distribution['card_gross_cents']),
                'card_fee_amount' => Money::toDatabase($distribution['card_fee_cents']),
                'card_net_amount' => Money::toDatabase($distribution['card_net_cents']),
                'total_amount' => Money::toDatabase($distribution['total_cents']),
            ]);
        }
    }

    private function lockShift(PDO $connection, int $shiftId, int $restaurantId): ?array
    {
        $statement = $connection->prepare(
            'SELECT id, status
             FROM shifts
             WHERE id = :id AND restaurant_id = :restaurant_id
             LIMIT 1 FOR UPDATE'
        );
        $statement->execute([
            'id' => $shiftId,
            'restaurant_id' => $restaurantId,
        ]);
        $shift = $statement->fetch(PDO::FETCH_ASSOC);

        return $shift ?: null;
    }

    private function lockEntry(PDO $connection, int $entryId, int $restaurantId): ?array
    {
        $statement = $connection->prepare(
            'SELECT id, shift_id, status
             FROM tip_entries
             WHERE id = :id AND restaurant_id = :restaurant_id
             LIMIT 1 FOR UPDATE'
        );
        $statement->execute([
            'id' => $entryId,
            'restaurant_id' => $restaurantId,
        ]);
        $entry = $statement->fetch(PDO::FETCH_ASSOC);

        return $entry ?: null;
    }

    private function entryExistsForShift(PDO $connection, int $shiftId, int $restaurantId): bool
    {
        $statement = $connection->prepare(
            'SELECT id FROM tip_entries
             WHERE shift_id = :shift_id AND restaurant_id = :restaurant_id
             LIMIT 1'
        );
        $statement->execute([
            'shift_id' => $shiftId,
            'restaurant_id' => $restaurantId,
        ]);

        return (bool) $statement->fetchColumn();
    }

    private function shiftEmployeeIds(PDO $connection, int $shiftId, int $restaurantId): array
    {
        $statement = $connection->prepare(
            'SELECT employee_id
             FROM shift_employees
             WHERE shift_id = :shift_id AND restaurant_id = :restaurant_id
             ORDER BY employee_id ASC'
        );
        $statement->execute([
            'shift_id' => $shiftId,
            'restaurant_id' => $restaurantId,
        ]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }
}
