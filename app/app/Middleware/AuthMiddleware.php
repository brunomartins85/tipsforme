<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\SessionSecurity;

final class AuthMiddleware
{
    public function handle(): bool
    {
        if (empty($_SESSION['auth_user'])) {
            flash('error', trans('auth.required'));
            redirect('/login');
        }

        if (!SessionSecurity::validate()) {
            flash('error', trans('auth.session_expired'));
            redirect('/login');
        }

        return true;
    }
}
