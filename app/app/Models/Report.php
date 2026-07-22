<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final class Report
{
    public function monthly(int $restaurantId, string $month, ?int $employeeId = null): array
    {
        [$startDate, $endDate] = $this->period($month);

        return [
            'month' => $month,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'employee_summary' => $this->employeeSummary($restaurantId, $startDate, $endDate, $employeeId),
            'details' => $this->details($restaurantId, $startDate, $endDate, $employeeId),
            'settlements' => $this->settlements($restaurantId, $month, $employeeId),
            'totals' => $this->totals($restaurantId, $startDate, $endDate, $employeeId),
        ];
    }

    public function employeeBelongsToRestaurant(int $employeeId, int $restaurantId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT 1 FROM employees WHERE id = :id AND restaurant_id = :restaurant_id LIMIT 1'
        );
        $statement->execute([
            'id' => $employeeId,
            'restaurant_id' => $restaurantId,
        ]);

        return (bool) $statement->fetchColumn();
    }

    private function employeeSummary(
        int $restaurantId,
        string $startDate,
        string $endDate,
        ?int $employeeId
    ): array {
        $sql = <<<SQL
            SELECT
                employees.id AS employee_id,
                employees.name,
                employees.position,
                COUNT(DISTINCT tip_distributions.shift_id) AS shift_count,
                COALESCE(SUM(tip_distributions.cash_amount), 0) AS cash_total,
                COALESCE(SUM(tip_distributions.card_gross_amount), 0) AS card_gross_total,
                COALESCE(SUM(tip_distributions.card_fee_amount), 0) AS card_fee_total,
                COALESCE(SUM(tip_distributions.card_net_amount), 0) AS card_net_total,
                COALESCE(SUM(tip_distributions.total_amount), 0) AS total_amount,
                COALESCE(SUM(CASE
                    WHEN tip_distributions.cash_settlement_id IS NULL
                    THEN tip_distributions.cash_amount ELSE 0 END), 0) AS pending_cash,
                COALESCE(SUM(CASE
                    WHEN tip_distributions.card_settlement_id IS NULL
                    THEN tip_distributions.card_net_amount ELSE 0 END), 0) AS pending_card,
                COALESCE(SUM(CASE
                    WHEN tip_distributions.cash_settlement_id IS NOT NULL
                    THEN tip_distributions.cash_amount ELSE 0 END), 0) AS paid_cash,
                COALESCE(SUM(CASE
                    WHEN tip_distributions.card_settlement_id IS NOT NULL
                    THEN tip_distributions.card_net_amount ELSE 0 END), 0) AS paid_card
            FROM tip_distributions
            INNER JOIN shifts
                ON shifts.id = tip_distributions.shift_id
               AND shifts.restaurant_id = tip_distributions.restaurant_id
            INNER JOIN employees
                ON employees.id = tip_distributions.employee_id
               AND employees.restaurant_id = tip_distributions.restaurant_id
            WHERE tip_distributions.restaurant_id = :restaurant_id
              AND shifts.shift_date BETWEEN :start_date AND :end_date
        SQL;
        $parameters = [
            'restaurant_id' => $restaurantId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($employeeId !== null) {
            $sql .= ' AND tip_distributions.employee_id = :employee_id';
            $parameters['employee_id'] = $employeeId;
        }

        $sql .= <<<SQL
            GROUP BY employees.id, employees.name, employees.position
            ORDER BY employees.name ASC
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['pending_total'] = (float) $row['pending_cash'] + (float) $row['pending_card'];
            $row['paid_total'] = (float) $row['paid_cash'] + (float) $row['paid_card'];
        }
        unset($row);

        return $rows;
    }

    private function details(
        int $restaurantId,
        string $startDate,
        string $endDate,
        ?int $employeeId
    ): array {
        $sql = <<<SQL
            SELECT
                tip_distributions.id,
                tip_distributions.employee_id,
                employees.name,
                employees.position,
                shifts.shift_date,
                shifts.shift_type,
                tip_entries.id AS entry_id,
                tip_entries.card_fee_percentage,
                tip_distributions.cash_amount,
                tip_distributions.card_gross_amount,
                tip_distributions.card_fee_amount,
                tip_distributions.card_net_amount,
                tip_distributions.total_amount,
                tip_distributions.cash_settlement_id,
                tip_distributions.card_settlement_id
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
              AND shifts.shift_date BETWEEN :start_date AND :end_date
        SQL;
        $parameters = [
            'restaurant_id' => $restaurantId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($employeeId !== null) {
            $sql .= ' AND tip_distributions.employee_id = :employee_id';
            $parameters['employee_id'] = $employeeId;
        }

        $sql .= ' ORDER BY shifts.shift_date ASC, shifts.shift_type ASC, employees.name ASC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $cashPaid = (float) $row['cash_amount'] <= 0 || $row['cash_settlement_id'] !== null;
            $cardPaid = (float) $row['card_net_amount'] <= 0 || $row['card_settlement_id'] !== null;
            $row['payment_status'] = $cashPaid && $cardPaid
                ? 'paid'
                : (($row['cash_settlement_id'] !== null || $row['card_settlement_id'] !== null)
                    ? 'partial'
                    : 'pending');
        }
        unset($row);

        return $rows;
    }

    private function settlements(int $restaurantId, string $month, ?int $employeeId): array
    {
        $sql = <<<SQL
            SELECT
                settlements.id,
                settlements.settlement_type,
                settlements.reference_month,
                settlements.payment_date,
                settlements.status,
                settlement_payments.employee_id,
                employees.name,
                settlement_payments.cash_amount,
                settlement_payments.card_gross_amount,
                settlement_payments.card_fee_amount,
                settlement_payments.card_net_amount,
                settlement_payments.total_amount
            FROM settlement_payments
            INNER JOIN settlements
                ON settlements.id = settlement_payments.settlement_id
               AND settlements.restaurant_id = settlement_payments.restaurant_id
            INNER JOIN employees
                ON employees.id = settlement_payments.employee_id
               AND employees.restaurant_id = settlement_payments.restaurant_id
            WHERE settlement_payments.restaurant_id = :restaurant_id
              AND DATE_FORMAT(settlements.reference_month, '%Y-%m') = :reference_month
        SQL;
        $parameters = [
            'restaurant_id' => $restaurantId,
            'reference_month' => $month,
        ];

        if ($employeeId !== null) {
            $sql .= ' AND settlement_payments.employee_id = :employee_id';
            $parameters['employee_id'] = $employeeId;
        }

        $sql .= ' ORDER BY settlements.payment_date ASC, employees.name ASC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function totals(
        int $restaurantId,
        string $startDate,
        string $endDate,
        ?int $employeeId
    ): array {
        $sql = <<<SQL
            SELECT
                COUNT(DISTINCT tip_distributions.shift_id) AS shift_count,
                COUNT(DISTINCT tip_distributions.employee_id) AS employee_count,
                COALESCE(SUM(tip_distributions.cash_amount), 0) AS cash_total,
                COALESCE(SUM(tip_distributions.card_gross_amount), 0) AS card_gross_total,
                COALESCE(SUM(tip_distributions.card_fee_amount), 0) AS card_fee_total,
                COALESCE(SUM(tip_distributions.card_net_amount), 0) AS card_net_total,
                COALESCE(SUM(tip_distributions.total_amount), 0) AS total_amount,
                COALESCE(SUM(CASE
                    WHEN tip_distributions.cash_settlement_id IS NULL
                    THEN tip_distributions.cash_amount ELSE 0 END), 0) AS pending_cash,
                COALESCE(SUM(CASE
                    WHEN tip_distributions.card_settlement_id IS NULL
                    THEN tip_distributions.card_net_amount ELSE 0 END), 0) AS pending_card
            FROM tip_distributions
            INNER JOIN shifts
                ON shifts.id = tip_distributions.shift_id
               AND shifts.restaurant_id = tip_distributions.restaurant_id
            WHERE tip_distributions.restaurant_id = :restaurant_id
              AND shifts.shift_date BETWEEN :start_date AND :end_date
        SQL;
        $parameters = [
            'restaurant_id' => $restaurantId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($employeeId !== null) {
            $sql .= ' AND tip_distributions.employee_id = :employee_id';
            $parameters['employee_id'] = $employeeId;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);
        $totals = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
        $totals['pending_total'] = (float) ($totals['pending_cash'] ?? 0)
            + (float) ($totals['pending_card'] ?? 0);
        $totals['paid_total'] = (float) ($totals['total_amount'] ?? 0)
            - (float) $totals['pending_total'];

        return $totals;
    }

    private function period(string $month): array
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m', $month);

        if ($date === false || $date->format('Y-m') !== $month) {
            throw new RuntimeException('Invalid report month.');
        }

        return [
            $date->format('Y-m-01'),
            $date->modify('last day of this month')->format('Y-m-d'),
        ];
    }
}
