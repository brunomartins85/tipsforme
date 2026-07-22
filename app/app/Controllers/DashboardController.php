<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Employee;
use App\Models\Restaurant;
use App\Models\Shift;
use App\Models\TipEntry;

final class DashboardController
{
    public function index(): void
    {
        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $restaurant = (new Restaurant())->findById($restaurantId);
        $employeeModel = new Employee();
        $shiftModel = new Shift();
        $entryModel = new TipEntry();

        View::render('dashboard/index', [
            'user' => $user,
            'restaurant' => $restaurant,
            'activeEmployeeCount' => $employeeModel->countActive($restaurantId),
            'currentMonthShiftCount' => $shiftModel->countCurrentMonth($restaurantId),
            'entryTotals' => $entryModel->currentMonthTotals($restaurantId),
            'pendingTotals' => $entryModel->currentMonthPendingTotals($restaurantId),
            'recentEntries' => $entryModel->recentByRestaurant($restaurantId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }
}
