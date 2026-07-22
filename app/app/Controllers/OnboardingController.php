<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Models\Restaurant;
use App\Services\AuditLogger;

final class OnboardingController
{
    public function index(): void
    {
        $user = auth_user();

        if ($user === null || !in_array($user['role'] ?? '', ['admin', 'manager'], true)) {
            redirect('/login');
        }

        if (!empty($user['onboarding_completed_at'])) {
            redirect('/dashboard');
        }

        $restaurant = (new Restaurant())->findById((int) $user['restaurant_id']);

        View::render('onboarding/index', [
            'restaurant' => $restaurant ?? [],
            'errors' => $_SESSION['_onboarding_errors'] ?? [],
        ], 'auth');

        unset($_SESSION['_onboarding_errors']);
    }

    public function complete(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/onboarding');
        }

        $user = auth_user();

        if ($user === null || !in_array($user['role'] ?? '', ['admin', 'manager'], true)) {
            redirect('/login');
        }

        $data = [
            'default_card_fee' => str_replace(',', '.', trim((string) ($_POST['default_card_fee'] ?? '25'))),
            'first_half_closing_day' => (int) ($_POST['first_half_closing_day'] ?? 15),
            'timezone' => trim((string) ($_POST['timezone'] ?? 'Europe/Lisbon')),
            'default_language' => in_array(($_POST['default_language'] ?? ''), ['pt', 'en'], true)
                ? (string) $_POST['default_language']
                : 'pt',
        ];

        $errors = [];

        if (!is_numeric($data['default_card_fee']) || (float) $data['default_card_fee'] < 0 || (float) $data['default_card_fee'] > 100) {
            $errors['default_card_fee'] = trans('settings.validation.card_fee');
        }

        if ($data['first_half_closing_day'] < 1 || $data['first_half_closing_day'] > 28) {
            $errors['first_half_closing_day'] = trans('settings.validation.first_closing_day');
        }

        if (!in_array($data['timezone'], timezone_identifiers_list(), true)) {
            $errors['timezone'] = trans('settings.validation.timezone');
        }

        if ($errors !== []) {
            $_SESSION['_onboarding_errors'] = $errors;
            redirect('/onboarding');
        }

        (new Restaurant())->completeOnboarding((int) $user['restaurant_id'], $data);

        $_SESSION['auth_user']['restaurant_timezone'] = $data['timezone'];
        $_SESSION['auth_user']['onboarding_completed_at'] = date('Y-m-d H:i:s');
        $_SESSION['language'] = $data['default_language'];

        AuditLogger::record('restaurant.onboarding_completed', 'restaurant', (int) $user['restaurant_id']);
        flash('success', trans('onboarding.completed'));
        redirect('/dashboard');
    }
}
