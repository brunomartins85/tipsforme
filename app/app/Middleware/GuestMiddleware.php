<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\Restaurant;

final class GuestMiddleware
{
    public function handle(): bool
    {
        $user = auth_user();

        if ($user !== null) {
            if (($user['role'] ?? '') === 'employee') {
                redirect('/my/dashboard');
            }

            if (!array_key_exists('onboarding_completed_at', $user)) {
                $restaurant = (new Restaurant())->findById((int) $user['restaurant_id']);
                $_SESSION['auth_user']['onboarding_completed_at'] = $restaurant['onboarding_completed_at'] ?? null;
                $user = $_SESSION['auth_user'];
            }

            redirect(empty($user['onboarding_completed_at']) ? '/onboarding' : '/dashboard');
        }

        return true;
    }
}
