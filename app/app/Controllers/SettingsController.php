<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Locale;
use App\Core\SessionSecurity;
use App\Core\View;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\AuditLogger;

final class SettingsController
{

    public function index(): void
    {
        $this->render();
    }

    public function updateRestaurant(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/settings');
        }

        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'timezone' => trim((string) ($_POST['timezone'] ?? '')),
            'default_language' => trim((string) ($_POST['default_language'] ?? '')),
            'default_card_fee' => str_replace(',', '.', trim((string) ($_POST['default_card_fee'] ?? ''))),
            'first_half_closing_day' => (int) ($_POST['first_half_closing_day'] ?? 0),
            'password_reset_enabled' => isset($_POST['password_reset_enabled']) ? 1 : 0,
        ];
        $errors = [];

        if (text_length($data['name']) < 2 || text_length($data['name']) > 120) {
            $errors['name'] = trans('settings.validation.restaurant_name');
        }

        if (!in_array($data['timezone'], $this->timezones(), true)) {
            $errors['timezone'] = trans('settings.validation.timezone');
        }

        if (!in_array($data['default_language'], ['pt', 'en'], true)) {
            $errors['default_language'] = trans('settings.validation.language');
        }

        if (!is_numeric($data['default_card_fee'])
            || (float) $data['default_card_fee'] < 0
            || (float) $data['default_card_fee'] > 100
        ) {
            $errors['default_card_fee'] = trans('settings.validation.card_fee');
        }

        if ($data['first_half_closing_day'] < 1 || $data['first_half_closing_day'] > 28) {
            $errors['first_half_closing_day'] = trans('settings.validation.closing_day');
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render($errors, [], [], $data, 'restaurant');
            return;
        }

        $data['default_card_fee'] = number_format((float) $data['default_card_fee'], 2, '.', '');
        (new Restaurant())->updateSettings($restaurantId, $data);

        // Atualiza a sessão para refletir o nome imediatamente na sidebar.
        $_SESSION['auth_user']['restaurant_name'] = $data['name'];
        $_SESSION['auth_user']['restaurant_timezone'] = $data['timezone'];
        date_default_timezone_set($data['timezone']);
        AuditLogger::record('settings.restaurant', 'restaurant', $restaurantId, null, [
            'name' => $data['name'],
            'card_fee' => $data['default_card_fee'] . '%',
            'closing_day' => $data['first_half_closing_day'],
        ]);
        flash('success', trans('settings.restaurant_updated'));
        redirect('/settings');
    }

    public function updateCompany(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/settings');
        }

        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $data = [
            'legal_name' => trim((string) ($_POST['legal_name'] ?? '')),
            'trade_name' => trim((string) ($_POST['trade_name'] ?? '')),
            'country_code' => strtoupper(trim((string) ($_POST['country_code'] ?? ''))),
            'company_registration_number' => strtoupper(trim((string) ($_POST['company_registration_number'] ?? ''))),
            'vat_number' => strtoupper(preg_replace('/\s+/', '', (string) ($_POST['vat_number'] ?? '')) ?? ''),
            'business_type' => trim((string) ($_POST['business_type'] ?? '')),
            'address_line1' => trim((string) ($_POST['address_line1'] ?? '')),
            'address_line2' => trim((string) ($_POST['address_line2'] ?? '')),
            'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
        ];
        $errors = [];
        $countries = $this->countries();

        if (text_length($data['legal_name']) < 2 || text_length($data['legal_name']) > 160) {
            $errors['legal_name'] = trans('registration.validation.legal_name');
        }
        if ($data['trade_name'] !== '' && text_length($data['trade_name']) > 160) {
            $errors['trade_name'] = trans('registration.validation.trade_name');
        }
        if (!isset($countries[$data['country_code']])) {
            $errors['country_code'] = trans('registration.validation.country');
        }
        if (text_length($data['company_registration_number']) < 2 || text_length($data['company_registration_number']) > 80) {
            $errors['company_registration_number'] = trans('registration.validation.registration_number');
        }
        if ($data['vat_number'] !== '' && (text_length($data['vat_number']) < 4 || text_length($data['vat_number']) > 40)) {
            $errors['vat_number'] = trans('registration.validation.vat');
        }
        if (!in_array($data['business_type'], ['restaurant', 'cafe', 'bar', 'hotel', 'other'], true)) {
            $errors['business_type'] = trans('registration.validation.business_type');
        }
        if (text_length($data['address_line1']) < 3 || text_length($data['address_line1']) > 190) {
            $errors['address_line1'] = trans('registration.validation.address');
        }
        if (text_length($data['address_line2']) > 190) {
            $errors['address_line2'] = trans('registration.validation.address');
        }
        if (text_length($data['postal_code']) < 2 || text_length($data['postal_code']) > 32) {
            $errors['postal_code'] = trans('registration.validation.postal_code');
        }
        if (text_length($data['city']) < 2 || text_length($data['city']) > 120) {
            $errors['city'] = trans('registration.validation.city');
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render([], [], [], [], 'company', [], $errors, $data);
            return;
        }

        (new Restaurant())->updateCompanyProfile($restaurantId, $data);
        $_SESSION['auth_user']['restaurant_name'] = $data['trade_name'] ?: $data['legal_name'];
        AuditLogger::record('settings.company', 'restaurant', $restaurantId, null, [
            'country_code' => $data['country_code'],
            'business_type' => $data['business_type'],
        ]);
        flash('success', trans('settings.company_updated'));
        redirect('/settings#company-settings');
    }

    public function updateProfile(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/settings');
        }

        $user = auth_user();
        $userId = (int) $user['id'];
        $restaurantId = (int) $user['restaurant_id'];
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => text_lower(trim((string) ($_POST['email'] ?? ''))),
            'language' => trim((string) ($_POST['language'] ?? '')),
        ];
        $errors = [];
        $model = new User();

        if (text_length($data['name']) < 2 || text_length($data['name']) > 120) {
            $errors['name'] = trans('settings.validation.profile_name');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = trans('settings.validation.email');
        } elseif ($model->emailExists($data['email'], $userId)) {
            $errors['email'] = trans('settings.validation.email_unique');
        }

        if (!in_array($data['language'], ['pt', 'en'], true)) {
            $errors['language'] = trans('settings.validation.language');
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render([], $errors, [], [], 'profile', $data);
            return;
        }

        $model->updateProfile($userId, $restaurantId, $data);
        $_SESSION['auth_user']['name'] = $data['name'];
        $_SESSION['auth_user']['email'] = $data['email'];
        $_SESSION['language'] = $data['language'];
        Locale::boot();
        AuditLogger::record('settings.profile', 'user', $userId, null, [
            'name' => $data['name'],
            'language' => $data['language'],
        ]);

        flash('success', trans('settings.profile_updated'));
        redirect('/settings');
    }

    public function updatePassword(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/settings');
        }

        $user = auth_user();
        $userId = (int) $user['id'];
        $restaurantId = (int) $user['restaurant_id'];
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmation = (string) ($_POST['new_password_confirmation'] ?? '');
        $errors = [];
        $model = new User();
        $record = $model->findByIdForSettings($userId, $restaurantId);

        if ($record === null || !password_verify($currentPassword, $record['password_hash'])) {
            $errors['current_password'] = trans('settings.validation.current_password');
        }

        if (strlen($newPassword) < 8 || strlen($newPassword) > 72) {
            $errors['new_password'] = trans('settings.validation.new_password');
        }

        if ($newPassword !== $confirmation) {
            $errors['new_password_confirmation'] = trans('settings.validation.password_confirmation');
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render([], [], $errors, [], 'password');
            return;
        }

        $model->updatePassword(
            $userId,
            $restaurantId,
            password_hash($newPassword, PASSWORD_DEFAULT)
        );
        session_regenerate_id(true);
        SessionSecurity::markLogin();
        AuditLogger::record('settings.password', 'user', $userId);

        flash('success', trans('settings.password_updated'));
        redirect('/settings');
    }

    private function render(
        array $restaurantErrors = [],
        array $profileErrors = [],
        array $passwordErrors = [],
        array $restaurantOld = [],
        string $activeSection = 'restaurant',
        array $profileOld = [],
        array $companyErrors = [],
        array $companyOld = []
    ): void {
        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $userId = (int) $user['id'];
        $restaurant = (new Restaurant())->findById($restaurantId) ?? [];
        $profile = (new User())->findByIdForSettings($userId, $restaurantId) ?? $user;

        View::render('settings/index', [
            'user' => $user,
            'restaurant' => array_merge($restaurant, $restaurantOld, $companyOld),
            'profile' => array_merge($profile, $profileOld),
            'restaurantErrors' => $restaurantErrors,
            'profileErrors' => $profileErrors,
            'passwordErrors' => $passwordErrors,
            'activeSection' => $activeSection,
            'timezones' => $this->timezones(),
            'countries' => $this->countries(),
            'companyErrors' => $companyErrors,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    private function countries(): array
    {
        $countries = require dirname(__DIR__, 2) . '/config/europe.php';
        $language = Locale::current();
        uasort($countries, static fn (array $a, array $b): int => strcmp($a[$language], $b[$language]));

        return $countries;
    }

    private function timezones(): array
    {
        $countries = require dirname(__DIR__, 2) . '/config/europe.php';
        $timezones = array_values(array_unique(array_column($countries, 'timezone')));
        sort($timezones);

        return $timezones;
    }

}
