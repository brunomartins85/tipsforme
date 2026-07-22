<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\SessionSecurity;
use App\Core\View;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LoginThrottle;

final class AuthController
{
    public function home(): void
    {
        $user = auth_user();

        if (($user['role'] ?? '') === 'employee') {
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

        redirect('/dashboard');
    }

    public function showLogin(): void
    {
        View::render('auth/login', [
            'error' => flash('error'),
            'success' => flash('success'),
            'email' => $_SESSION['_old_email'] ?? '',
        ], 'auth');

        unset($_SESSION['_old_email']);
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/login');
        }

        $email = text_lower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $ipAddress = client_ip();
        $throttle = new LoginThrottle();
        $_SESSION['_old_email'] = $email;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            if ($email !== '') {
                $throttle->record($email, $ipAddress, false);
            }
            flash('error', trans('auth.invalid_credentials'));
            redirect('/login');
        }

        if ($throttle->isBlocked($email, $ipAddress)) {
            flash('error', trans('auth.too_many_attempts', [
                'minutes' => (string) max(1, (int) env('LOGIN_LOCK_MINUTES', '15')),
            ]));
            redirect('/login');
        }

        $model = new User();
        $user = $model->findActiveByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $throttle->record($email, $ipAddress, false);
            flash('error', trans('auth.invalid_credentials'));
            redirect('/login');
        }

        if ($user['role'] === 'employee' && empty($user['employee_id'])) {
            $throttle->record($email, $ipAddress, false);
            flash('error', trans('auth.employee_not_linked'));
            redirect('/login');
        }

        $throttle->record($email, $ipAddress, true);
        session_regenerate_id(true);

        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'restaurant_id' => (int) $user['restaurant_id'],
            'employee_id' => !empty($user['employee_id']) ? (int) $user['employee_id'] : null,
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'employee_position' => $user['employee_position'] ?? null,
            'restaurant_name' => $user['restaurant_name'],
            'restaurant_slug' => $user['restaurant_slug'],
            'restaurant_timezone' => $user['restaurant_timezone'] ?? env('APP_TIMEZONE', 'Europe/Lisbon'),
            'onboarding_completed_at' => $user['onboarding_completed_at'] ?? null,
        ];

        $_SESSION['language'] = in_array($user['language'], ['pt', 'en'], true)
            ? $user['language']
            : 'pt';

        SessionSecurity::markLogin();
        unset($_SESSION['_old_email']);
        $model->updateLastLogin((int) $user['id'], (int) $user['restaurant_id']);
        AuditLogger::recordFor(
            (int) $user['restaurant_id'],
            (int) $user['id'],
            'auth.login',
            'user',
            (int) $user['id']
        );

        if ($user['role'] === 'employee') {
            redirect('/my/dashboard');
        }

        redirect(empty($user['onboarding_completed_at']) ? '/onboarding' : '/dashboard');
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $this->home();
        }

        AuditLogger::record('auth.logout', 'user', (int) (auth_user()['id'] ?? 0));
        SessionSecurity::destroy();
        redirect('/login');
    }
}
