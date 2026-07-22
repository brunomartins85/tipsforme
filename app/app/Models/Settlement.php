<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Support\Money;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use Throwable;

final class Settlement
{
    public const FIRST_HALF = 'first_half';
    public const MONTH_END = 'month_end';

    public function preview(int $restaurantId, string $type, string $referenceMonth): array
    {
        $firstHalfClosingDay = $this->firstHalfClosingDay($restaurantId);
        $periods = $this->periods($type, $referenceMonth, $firstHalfClosingDay);
        $rows = $this->pendingRows(Database::connection(), $restaurantId, $periods, false);
        $summary = $this->buildSummary($type, $referenceMonth, $periods, $rows);
        $summary['availability'] = $this->availability(
            $type,
            $referenceMonth,
            $firstHalfClosingDay
        );

        return $summary;
    }

    public function create(
        int $restaurantId,
        int $userId,
        string $type,
        string $referenceMonth,
        string $paymentDate,
        ?string $notes
    ): int {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $firstHalfClosingDay = $this->firstHalfClosingDay($restaurantId);
            $availability = $this->availability($type, $referenceMonth, $firstHalfClosingDay);

            if (!$availability['available']) {
                throw new RuntimeException('This settlement is not available yet.');
            }

            $periods = $this->periods($type, $referenceMonth, $firstHalfClosingDay);
            $rows = $this->pendingRows($connection, $restaurantId, $periods, true);
            $summary = $this->buildSummary($type, $referenceMonth, $periods, $rows);

            if ($summary['totals']['total_cents'] <= 0 || $summary['payments'] === []) {
                throw new RuntimeException('There are no pending amounts for this settlement.');
            }

            $statement = $connection->prepare(
                'INSERT INTO settlements
                    (restaurant_id, settlement_type, reference_month,
                     cash_period_start, cash_period_end, card_period_start, card_period_end,
                     payment_date, cash_total, card_gross_total, card_fee_total,
                     card_net_total, total_paid, employee_count, status, notes, created_by)
                 VALUES
                    (:restaurant_id, :settlement_type, :reference_month,
                     :cash_period_start, :cash_period_end, :card_period_start, :card_period_end,
                     :payment_date, :cash_total, :card_gross_total, :card_fee_total,
                     :card_net_total, :total_paid, :employee_count, :status, :notes, :created_by)'
            );
            $statement->execute([
                'restaurant_id' => $restaurantId,
                'settlement_type' => $type,
                'reference_month' => $periods['month_start'],
                'cash_period_start' => $periods['cash_start'],
                'cash_period_end' => $periods['cash_end'],
                'card_period_start' => $periods['card_start'],
                'card_period_end' => $periods['card_end'],
                'payment_date' => $paymentDate,
                'cash_total' => Money::toDatabase($summary['totals']['cash_cents']),
                'card_gross_total' => Money::toDatabase($summary['totals']['card_gross_cents']),
                'card_fee_total' => Money::toDatabase($summary['totals']['card_fee_cents']),
                'card_net_total' => Money::toDatabase($summary['totals']['card_net_cents']),
                'total_paid' => Money::toDatabase($summary['totals']['total_cents']),
                'employee_count' => count($summary['payments']),
                'status' => 'paid',
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            $settlementId = (int) $connection->lastInsertId();
            $this->insertPayments(
                $connection,
                $settlementId,
                $restaurantId,
                $paymentDate,
                $summary['payments']
            );

            $this->markDistributionsPaid($connection, $settlementId, $restaurantId, $summary['rows']);
            $this->refreshEntryStatuses(
                $connection,
                array_values(array_unique(array_map(
                    static fn (array $row): int => (int) $row['tip_entry_id'],
                    $summary['rows']
                ))),
                $restaurantId,
                $userId
            );

            $connection->commit();

            return $settlementId;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function history(int $restaurantId, int $limit = 30): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                settlements.id,
                settlements.settlement_type,
                settlements.reference_month,
                settlements.cash_period_start,
                settlements.cash_period_end,
                settlements.card_period_start,
                settlements.card_period_end,
                settlements.payment_date,
                settlements.cash_total,
                settlements.card_gross_total,
                settlements.card_fee_total,
                settlements.card_net_total,
                settlements.total_paid,
                settlements.employee_count,
                settlements.status,
                settlements.created_at
             FROM settlements
             WHERE settlements.restaurant_id = :restaurant_id
             ORDER BY settlements.payment_date DESC, settlements.id DESC
             LIMIT :limit'
        );
        $statement->bindValue(':restaurant_id', $restaurantId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $settlementId, int $restaurantId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                settlements.*,
                users.name AS created_by_name
             FROM settlements
             INNER JOIN users ON users.id = settlements.created_by
             WHERE settlements.id = :id
               AND settlements.restaurant_id = :restaurant_id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $settlementId,
            'restaurant_id' => $restaurantId,
        ]);
        $settlement = $statement->fetch(PDO::FETCH_ASSOC);

        return $settlement ?: null;
    }

    public function payments(int $settlementId, int $restaurantId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                settlement_payments.id,
                settlement_payments.employee_id,
                settlement_payments.cash_amount,
                settlement_payments.card_gross_amount,
                settlement_payments.card_fee_amount,
                settlement_payments.card_net_amount,
                settlement_payments.total_amount,
                settlement_payments.payment_date,
                settlement_payments.status,
                employees.name,
                employees.position
             FROM settlement_payments
             INNER JOIN employees
                ON employees.id = settlement_payments.employee_id
               AND employees.restaurant_id = settlement_payments.restaurant_id
             WHERE settlement_payments.settlement_id = :settlement_id
               AND settlement_payments.restaurant_id = :restaurant_id
             ORDER BY employees.name ASC'
        );
        $statement->execute([
            'settlement_id' => $settlementId,
            'restaurant_id' => $restaurantId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentEmployeePayments(int $restaurantId, int $limit = 8): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                settlement_payments.id,
                settlement_payments.settlement_id,
                settlement_payments.total_amount,
                settlement_payments.payment_date,
                employees.name,
                settlements.settlement_type,
                settlements.reference_month
             FROM settlement_payments
             INNER JOIN employees
                ON employees.id = settlement_payments.employee_id
               AND employees.restaurant_id = settlement_payments.restaurant_id
             INNER JOIN settlements
                ON settlements.id = settlement_payments.settlement_id
               AND settlements.restaurant_id = settlement_payments.restaurant_id
             WHERE settlement_payments.restaurant_id = :restaurant_id
             ORDER BY settlement_payments.payment_date DESC, settlement_payments.id DESC
             LIMIT :limit'
        );
        $statement->bindValue(':restaurant_id', $restaurantId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function periods(string $type, string $referenceMonth, int $firstHalfClosingDay = 15): array
    {
        if (!in_array($type, [self::FIRST_HALF, self::MONTH_END], true)) {
            throw new RuntimeException('Invalid settlement type.');
        }

        $month = DateTimeImmutable::createFromFormat('!Y-m', $referenceMonth);

        if ($month === false || $month->format('Y-m') !== $referenceMonth) {
            throw new RuntimeException('Invalid reference month.');
        }

        $firstHalfClosingDay = max(1, min(28, $firstHalfClosingDay));
        $monthStart = $month->format('Y-m-01');
        $monthEnd = $month->modify('last day of this month')->format('Y-m-d');
        $firstHalfEnd = $month->setDate((int) $month->format('Y'), (int) $month->format('m'), $firstHalfClosingDay);
        $secondHalfStart = $firstHalfEnd->modify('+1 day');

        if ($type === self::FIRST_HALF) {
            return [
                'month_start' => $monthStart,
                'month_end' => $monthEnd,
                'cash_start' => $monthStart,
                'cash_end' => $firstHalfEnd->format('Y-m-d'),
                'card_start' => null,
                'card_end' => null,
                'include_card' => false,
            ];
        }

        return [
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
            'cash_start' => $secondHalfStart->format('Y-m-d'),
            'cash_end' => $monthEnd,
            'card_start' => $monthStart,
            'card_end' => $monthEnd,
            'include_card' => true,
        ];
    }


    public function availability(
        string $type,
        string $referenceMonth,
        int $firstHalfClosingDay = 15,
        ?DateTimeImmutable $today = null
    ): array {
        $periods = $this->periods($type, $referenceMonth, $firstHalfClosingDay);
        $today = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0);
        $referenceStart = new DateTimeImmutable($periods['month_start']);
        $currentMonthStart = $today->modify('first day of this month');
        $availableOn = new DateTimeImmutable(
            $type === self::FIRST_HALF
                ? $periods['cash_end']
                : $periods['month_end']
        );

        if ($referenceStart > $currentMonthStart) {
            return [
                'available' => false,
                'available_on' => $availableOn->format('Y-m-d'),
                'reason' => 'future_month',
            ];
        }

        if ($referenceStart < $currentMonthStart || $today >= $availableOn) {
            return [
                'available' => true,
                'available_on' => $availableOn->format('Y-m-d'),
                'reason' => null,
            ];
        }

        return [
            'available' => false,
            'available_on' => $availableOn->format('Y-m-d'),
            'reason' => 'too_early',
        ];
    }


    private function firstHalfClosingDay(int $restaurantId): int
    {
        $statement = Database::connection()->prepare(
            'SELECT first_half_closing_day FROM restaurants WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $restaurantId]);

        return max(1, min(28, (int) ($statement->fetchColumn() ?: 15)));
    }

    private function pendingRows(PDO $connection, int $restaurantId, array $periods, bool $lock): array
    {
        if ($periods['include_card']) {
            $sql = <<<SQL
                SELECT
                    tip_distributions.id AS distribution_id,
                    tip_distributions.tip_entry_id,
                    tip_distributions.employee_id,
                    tip_distributions.cash_amount,
                    tip_distributions.card_gross_amount,
                    tip_distributions.card_fee_amount,
                    tip_distributions.card_net_amount,
                    tip_distributions.cash_settlement_id,
                    tip_distributions.card_settlement_id,
                    shifts.shift_date,
                    employees.name,
                    employees.position
                FROM tip_distributions
                INNER JOIN tip_entries
                    ON tip_entries.id = tip_distributions.tip_entry_id
                   AND tip_entries.restaurant_id = tip_distributions.restaurant_id
                INNER JOIN shifts
                    ON shifts.id = tip_distributions.shift_id
                   AND shifts.restaurant_id = tip_distributions.restaurant_id
                INNER JOIN employees
                    ON employees.id = tip_distributions.employee_id
                   AND employees.restaurant_id = tip_distributions.restaurant_id
                WHERE tip_distributions.restaurant_id = :restaurant_id
                  AND (
                    (
                        shifts.shift_date BETWEEN :cash_start AND :cash_end
                        AND tip_distributions.cash_amount > 0
                        AND tip_distributions.cash_settlement_id IS NULL
                    )
                    OR
                    (
                        shifts.shift_date BETWEEN :card_start AND :card_end
                        AND tip_distributions.card_net_amount > 0
                        AND tip_distributions.card_settlement_id IS NULL
                    )
                  )
                ORDER BY employees.name ASC, shifts.shift_date ASC, tip_distributions.id ASC
            SQL;
            $parameters = [
                'restaurant_id' => $restaurantId,
                'cash_start' => $periods['cash_start'],
                'cash_end' => $periods['cash_end'],
                'card_start' => $periods['card_start'],
                'card_end' => $periods['card_end'],
            ];
        } else {
            $sql = <<<SQL
                SELECT
                    tip_distributions.id AS distribution_id,
                    tip_distributions.tip_entry_id,
                    tip_distributions.employee_id,
                    tip_distributions.cash_amount,
                    tip_distributions.card_gross_amount,
                    tip_distributions.card_fee_amount,
                    tip_distributions.card_net_amount,
                    tip_distributions.cash_settlement_id,
                    tip_distributions.card_settlement_id,
                    shifts.shift_date,
                    employees.name,
                    employees.position
                FROM tip_distributions
                INNER JOIN tip_entries
                    ON tip_entries.id = tip_distributions.tip_entry_id
                   AND tip_entries.restaurant_id = tip_distributions.restaurant_id
                INNER JOIN shifts
                    ON shifts.id = tip_distributions.shift_id
                   AND shifts.restaurant_id = tip_distributions.restaurant_id
                INNER JOIN employees
                    ON employees.id = tip_distributions.employee_id
                   AND employees.restaurant_id = tip_distributions.restaurant_id
                WHERE tip_distributions.restaurant_id = :restaurant_id
                  AND shifts.shift_date BETWEEN :cash_start AND :cash_end
                  AND tip_distributions.cash_amount > 0
                  AND tip_distributions.cash_settlement_id IS NULL
                ORDER BY employees.name ASC, shifts.shift_date ASC, tip_distributions.id ASC
            SQL;
            $parameters = [
                'restaurant_id' => $restaurantId,
                'cash_start' => $periods['cash_start'],
                'cash_end' => $periods['cash_end'],
            ];
        }

        if ($lock) {
            $sql .= ' FOR UPDATE';
        }

        $statement = $connection->prepare($sql);
        $statement->execute($parameters);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $date = (string) $row['shift_date'];
            $cashEligible = $date >= $periods['cash_start']
                && $date <= $periods['cash_end']
                && $row['cash_settlement_id'] === null;
            $cardEligible = $periods['include_card']
                && $periods['card_start'] !== null
                && $periods['card_end'] !== null
                && $date >= $periods['card_start']
                && $date <= $periods['card_end']
                && $row['card_settlement_id'] === null;

            $row['cash_due_cents'] = $cashEligible
                ? Money::databaseToCents($row['cash_amount'])
                : 0;
            $row['card_gross_due_cents'] = $cardEligible
                ? Money::databaseToCents($row['card_gross_amount'])
                : 0;
            $row['card_fee_due_cents'] = $cardEligible
                ? Money::databaseToCents($row['card_fee_amount'])
                : 0;
            $row['card_net_due_cents'] = $cardEligible
                ? Money::databaseToCents($row['card_net_amount'])
                : 0;
            $row['total_due_cents'] = $row['cash_due_cents'] + $row['card_net_due_cents'];
        }
        unset($row);

        return array_values(array_filter(
            $rows,
            static fn (array $row): bool => $row['total_due_cents'] > 0
        ));
    }

    private function buildSummary(string $type, string $referenceMonth, array $periods, array $rows): array
    {
        $payments = [];
        $totals = [
            'cash_cents' => 0,
            'card_gross_cents' => 0,
            'card_fee_cents' => 0,
            'card_net_cents' => 0,
            'total_cents' => 0,
        ];

        foreach ($rows as $row) {
            $employeeId = (int) $row['employee_id'];

            if (!isset($payments[$employeeId])) {
                $payments[$employeeId] = [
                    'employee_id' => $employeeId,
                    'name' => $row['name'],
                    'position' => $row['position'],
                    'cash_cents' => 0,
                    'card_gross_cents' => 0,
                    'card_fee_cents' => 0,
                    'card_net_cents' => 0,
                    'total_cents' => 0,
                ];
            }

            foreach (['cash', 'card_gross', 'card_fee', 'card_net', 'total'] as $key) {
                $payments[$employeeId][$key . '_cents'] += (int) $row[$key . '_due_cents'];
                $totals[$key . '_cents'] += (int) $row[$key . '_due_cents'];
            }
        }

        uasort($payments, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

        return [
            'type' => $type,
            'reference_month' => $referenceMonth,
            'periods' => $periods,
            'rows' => $rows,
            'payments' => array_values($payments),
            'totals' => $totals,
            'distribution_count' => count($rows),
        ];
    }

    private function insertPayments(
        PDO $connection,
        int $settlementId,
        int $restaurantId,
        string $paymentDate,
        array $payments
    ): void {
        $statement = $connection->prepare(
            'INSERT INTO settlement_payments
                (restaurant_id, settlement_id, employee_id, cash_amount,
                 card_gross_amount, card_fee_amount, card_net_amount,
                 total_amount, payment_date, status)
             VALUES
                (:restaurant_id, :settlement_id, :employee_id, :cash_amount,
                 :card_gross_amount, :card_fee_amount, :card_net_amount,
                 :total_amount, :payment_date, :status)'
        );

        foreach ($payments as $payment) {
            $statement->execute([
                'restaurant_id' => $restaurantId,
                'settlement_id' => $settlementId,
                'employee_id' => $payment['employee_id'],
                'cash_amount' => Money::toDatabase($payment['cash_cents']),
                'card_gross_amount' => Money::toDatabase($payment['card_gross_cents']),
                'card_fee_amount' => Money::toDatabase($payment['card_fee_cents']),
                'card_net_amount' => Money::toDatabase($payment['card_net_cents']),
                'total_amount' => Money::toDatabase($payment['total_cents']),
                'payment_date' => $paymentDate,
                'status' => 'paid',
            ]);
        }
    }

    private function markDistributionsPaid(
        PDO $connection,
        int $settlementId,
        int $restaurantId,
        array $rows
    ): void {
        $cashStatement = $connection->prepare(
            'UPDATE tip_distributions
             SET cash_settlement_id = :settlement_id
             WHERE id = :distribution_id
               AND restaurant_id = :restaurant_id
               AND cash_settlement_id IS NULL'
        );
        $cardStatement = $connection->prepare(
            'UPDATE tip_distributions
             SET card_settlement_id = :settlement_id
             WHERE id = :distribution_id
               AND restaurant_id = :restaurant_id
               AND card_settlement_id IS NULL'
        );

        foreach ($rows as $row) {
            if ($row['cash_due_cents'] > 0) {
                $cashStatement->execute([
                    'settlement_id' => $settlementId,
                    'distribution_id' => $row['distribution_id'],
                    'restaurant_id' => $restaurantId,
                ]);
            }

            if ($row['card_net_due_cents'] > 0) {
                $cardStatement->execute([
                    'settlement_id' => $settlementId,
                    'distribution_id' => $row['distribution_id'],
                    'restaurant_id' => $restaurantId,
                ]);
            }
        }
    }

    private function refreshEntryStatuses(
        PDO $connection,
        array $entryIds,
        int $restaurantId,
        int $userId
    ): void {
        $statusStatement = $connection->prepare(
            'SELECT
                SUM(CASE
                    WHEN (cash_amount > 0 AND cash_settlement_id IS NULL)
                      OR (card_net_amount > 0 AND card_settlement_id IS NULL)
                    THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE
                    WHEN cash_settlement_id IS NOT NULL OR card_settlement_id IS NOT NULL
                    THEN 1 ELSE 0 END) AS paid_count
             FROM tip_distributions
             WHERE tip_entry_id = :entry_id
               AND restaurant_id = :restaurant_id'
        );
        $updateStatement = $connection->prepare(
            'UPDATE tip_entries
             SET status = :status, updated_by = :updated_by
             WHERE id = :entry_id AND restaurant_id = :restaurant_id'
        );

        foreach ($entryIds as $entryId) {
            $statusStatement->execute([
                'entry_id' => $entryId,
                'restaurant_id' => $restaurantId,
            ]);
            $counts = $statusStatement->fetch(PDO::FETCH_ASSOC) ?: [];
            $pendingCount = (int) ($counts['pending_count'] ?? 0);
            $paidCount = (int) ($counts['paid_count'] ?? 0);
            $status = $pendingCount === 0
                ? 'settled'
                : ($paidCount > 0 ? 'partially_settled' : 'open');

            $updateStatement->execute([
                'status' => $status,
                'updated_by' => $userId,
                'entry_id' => $entryId,
                'restaurant_id' => $restaurantId,
            ]);
        }
    }
}
