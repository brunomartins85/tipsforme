<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Employee;
use App\Models\EmployeePortal;
use App\Models\Restaurant;
use DateTimeImmutable;

final class EmployeePortalController
{
    public function dashboard(): void
    {
        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $employeeId = (int) $user['employee_id'];
        $employee = (new Employee())->findById($employeeId, $restaurantId);
        $model = new EmployeePortal();
        $restaurant = (new Restaurant())->findById($restaurantId);

        View::render('employee/dashboard', [
            'employee' => $employee,
            'balance' => $model->balance($restaurantId, $employeeId),
            'monthTotals' => $model->currentMonthTotals($restaurantId, $employeeId),
            'recentDistributions' => $model->distributions($restaurantId, $employeeId, 6),
            'nextPaymentDate' => $this->nextPaymentDate((int) ($restaurant['first_half_closing_day'] ?? 15)),
            'success' => flash('success'),
            'error' => flash('error'),
        ], 'employee');
    }

    public function statement(): void
    {
        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $employeeId = (int) $user['employee_id'];

        View::render('employee/statement', [
            'employee' => (new Employee())->findById($employeeId, $restaurantId),
            'distributions' => (new EmployeePortal())->distributions($restaurantId, $employeeId),
        ], 'employee');
    }

    public function payments(): void
    {
        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $employeeId = (int) $user['employee_id'];

        View::render('employee/payments', [
            'employee' => (new Employee())->findById($employeeId, $restaurantId),
            'payments' => (new EmployeePortal())->payments($restaurantId, $employeeId),
        ], 'employee');
    }

    private function nextPaymentDate(int $firstHalfClosingDay): string
    {
        $today = new DateTimeImmutable('today');
        $firstHalfClosingDay = max(1, min(28, $firstHalfClosingDay));
        $firstClosing = $today->setDate(
            (int) $today->format('Y'),
            (int) $today->format('m'),
            $firstHalfClosingDay
        );

        if ($today <= $firstClosing) {
            return $firstClosing->format('Y-m-d');
        }

        return $today->modify('last day of this month')->format('Y-m-d');
    }
}
