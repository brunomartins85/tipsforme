<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\AuditLog;

final class AuditLogController
{
    public function index(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $action = trim((string) ($_GET['action'] ?? ''));
        $model = new AuditLog();
        $availableActions = $model->actionsByRestaurant($restaurantId);

        if ($action !== '' && !in_array($action, $availableActions, true)) {
            $action = '';
        }

        View::render('audit/index', [
            'logs' => $model->latestByRestaurant($restaurantId, $action !== '' ? $action : null),
            'actions' => $availableActions,
            'selectedAction' => $action,
        ]);
    }
}
