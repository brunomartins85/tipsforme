<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\SessionSecurity;
use App\Models\Restaurant;

final class ManagerMiddleware
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

        if (!in_array($user['role'] ?? '', ['admin', 'manager'], true)) {
            flash('error', trans('auth.forbidden'));
            redirect('/my/dashboard');
        }

        if (!array_key_exists('onboarding_completed_at', $user)) {
            $restaurant = (new Restaurant())->findById((int) $user['restaurant_id']);
            $_SESSION['auth_user']['onboarding_completed_at'] = $restaurant['onboarding_completed_at'] ?? null;
            $user = $_SESSION['auth_user'];
        }

        if (empty($user['onboarding_completed_at'])) {
            redirect('/onboarding');
        }

        return true;
    }
}
