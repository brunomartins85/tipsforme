<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class EmployeePortal
{
    public function balance(int $restaurantId, int $employeeId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN cash_settlement_id IS NULL THEN cash_amount ELSE 0 END), 0) AS cash_pending,
                COALESCE(SUM(CASE WHEN card_settlement_id IS NULL THEN card_gross_amount ELSE 0 END), 0) AS card_gross_pending,
                COALESCE(SUM(CASE WHEN card_settlement_id IS NULL THEN card_fee_amount ELSE 0 END), 0) AS card_fee_pending,
                COALESCE(SUM(CASE WHEN card_settlement_id IS NULL THEN card_net_amount ELSE 0 END), 0) AS card_net_pending,
                COALESCE(SUM(
                    CASE WHEN cash_settlement_id IS NULL THEN cash_amount ELSE 0 END
                    + CASE WHEN card_settlement_id IS NULL THEN card_net_amount ELSE 0 END
                ), 0) AS total_pending
             FROM tip_distributions
             WHERE restaurant_id = :restaurant_id
               AND employee_id = :employee_id'
        );
        $statement->execute([
            'restaurant_id' => $restaurantId,
            'employee_id' => $employeeId,
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [
            'cash_pending' => '0.00',
            'card_gross_pending' => '0.00',
            'card_fee_pending' => '0.00',
            'card_net_pending' => '0.00',
            'total_pending' => '0.00',
        ];
    }

    public function currentMonthTotals(int $restaurantId, int $employeeId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                COUNT(tip_distributions.id) AS shift_count,
                COALESCE(SUM(tip_distributions.cash_amount), 0) AS cash_total,
                COALESCE(SUM(tip_distributions.card_net_amount), 0) AS card_net_total,
                COALESCE(SUM(tip_distributions.total_amount), 0) AS total_amount
             FROM tip_distributions
             INNER JOIN shifts
                ON shifts.id = tip_distributions.shift_id
               AND shifts.restaurant_id = tip_distributions.restaurant_id
             WHERE tip_distributions.restaurant_id = :restaurant_id
               AND tip_distributions.employee_id = :employee_id
               AND shifts.shift_date >= DATE_FORMAT(CURRENT_DATE(), \'%Y-%m-01\')
               AND shifts.shift_date < DATE_ADD(LAST_DAY(CURRENT_DATE()), INTERVAL 1 DAY)'
        );
        $statement->execute([
            'restaurant_id' => $restaurantId,
            'employee_id' => $employeeId,
        ]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function distributions(int $restaurantId, int $employeeId, int $limit = 100): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                tip_distributions.id,
                tip_distributions.cash_amount,
                tip_distributions.card_gross_amount,
                tip_distributions.card_fee_amount,
                tip_distributions.card_net_amount,
                tip_distributions.total_amount,
                tip_distributions.cash_settlement_id,
                tip_distributions.card_settlement_id,
                tip_entries.card_fee_percentage,
                shifts.shift_date,
                shifts.shift_type,
                (
                    SELECT COUNT(*)
                    FROM shift_employees
                    WHERE shift_employees.shift_id = shifts.id
                      AND shift_employees.restaurant_id = shifts.restaurant_id
                ) AS participant_count
             FROM tip_distributions
             INNER JOIN tip_entries
                ON tip_entries.id = tip_distributions.tip_entry_id
               AND tip_entries.restaurant_id = tip_distributions.restaurant_id
             INNER JOIN shifts
                ON shifts.id = tip_distributions.shift_id
               AND shifts.restaurant_id = tip_distributions.restaurant_id
             WHERE tip_distributions.restaurant_id = :restaurant_id
               AND tip_distributions.employee_id = :employee_id
             ORDER BY shifts.shift_date DESC, shifts.shift_type DESC, tip_distributions.id DESC
             LIMIT :limit'
        );
        $statement->bindValue(':restaurant_id', $restaurantId, PDO::PARAM_INT);
        $statement->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function payments(int $restaurantId, int $employeeId, int $limit = 100): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                settlement_payments.id,
                settlement_payments.settlement_id,
                settlement_payments.cash_amount,
                settlement_payments.card_gross_amount,
                settlement_payments.card_fee_amount,
                settlement_payments.card_net_amount,
                settlement_payments.total_amount,
                settlement_payments.payment_date,
                settlement_payments.status,
                settlements.settlement_type,
                settlements.reference_month,
                settlements.cash_period_start,
                settlements.cash_period_end,
                settlements.card_period_start,
                settlements.card_period_end
             FROM settlement_payments
             INNER JOIN settlements
                ON settlements.id = settlement_payments.settlement_id
               AND settlements.restaurant_id = settlement_payments.restaurant_id
             WHERE settlement_payments.restaurant_id = :restaurant_id
               AND settlement_payments.employee_id = :employee_id
             ORDER BY settlement_payments.payment_date DESC, settlement_payments.id DESC
             LIMIT :limit'
        );
        $statement->bindValue(':restaurant_id', $restaurantId, PDO::PARAM_INT);
        $statement->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
