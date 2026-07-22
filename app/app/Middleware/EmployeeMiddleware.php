<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\SessionSecurity;

final class EmployeeMiddleware
{
    public function handle(): bool
    {
        $user = auth_user();

        if ($user === null) {
            flash('error', trans('auth.required'));
            redirect('/login');
        }

        if (!SessionSecurity::validate()) {
            flash('error', trans('auth.session_expired'));
            redirect('/login');
        }

        if (($user['role'] ?? '') !== 'employee' || empty($user['employee_id'])) {
            http_response_code(403);
            flash('error', trans('auth.forbidden'));
            redirect('/dashboard');
        }

        return true;
    }
}
