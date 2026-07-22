<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Locale;
use App\Core\View;
use App\Models\CompanyRegistration;
use App\Services\EmailVerificationService;
use App\Services\RegistrationThrottle;
use App\Services\SmtpMailer;
use RuntimeException;
use Throwable;

final class RegistrationController
{
    public function show(): void
    {
        $_SESSION['_registration_started_at'] = time();

        View::render('auth/register', [
            'errors' => $_SESSION['_registration_errors'] ?? [],
            'old' => $_SESSION['_registration_old'] ?? $this->defaults(),
            'countries' => $this->countries(),
            'termsVersion' => $this->termsVersion(),
            'privacyVersion' => $this->privacyVersion(),
        ], 'auth');

        unset($_SESSION['_registration_errors'], $_SESSION['_registration_old']);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/register');
        }

        $ipAddress = client_ip();
        $throttle = new RegistrationThrottle();

        if ($throttle->isBlocked($ipAddress)) {
            flash('error', trans('registration.too_many_attempts'));
            redirect('/register');
        }

        $startedAt = (int) ($_SESSION['_registration_started_at'] ?? 0);
        $honeypot = trim((string) ($_POST['website'] ?? ''));

        if ($honeypot !== '' || $startedAt === 0 || (time() - $startedAt) < 2) {
            $throttle->record($ipAddress, false);
            flash('error', trans('registration.invalid_request'));
            redirect('/register');
        }

        $data = $this->sanitize($_POST);
        $errors = $this->validate($data);
        $model = new CompanyRegistration();

        if ($model->emailExists($data['admin_email'])) {
            $errors['admin_email'] = trans('registration.validation.email_unique');
        }

        if ($errors !== []) {
            $throttle->record($ipAddress, false);
            $_SESSION['_registration_errors'] = $errors;
            $_SESSION['_registration_old'] = $data;
            redirect('/register');
        }

        $data['terms_version'] = $this->termsVersion();
        $data['privacy_version'] = $this->privacyVersion();
        $data['registration_ip'] = $ipAddress;

        try {
            $account = $model->create($data);
            $verificationService = new EmailVerificationService();
            $token = $verificationService->issue($account['restaurant_id'], $account['user_id']);
            $emailSent = $this->sendVerificationEmail($data, $token);

            $throttle->record($ipAddress, true);
            $_SESSION['_pending_verification_email'] = $data['admin_email'];
            $_SESSION['_pending_verification_sent'] = $emailSent;

            redirect('/registration-pending');
        } catch (Throwable $exception) {
            error_log('Registration error: ' . $exception->getMessage());
            $throttle->record($ipAddress, false);
            $_SESSION['_registration_old'] = $data;
            flash('error', trans('registration.failed'));
            redirect('/register');
        }
    }

    public function pending(): void
    {
        $email = (string) ($_SESSION['_pending_verification_email'] ?? '');

        if ($email === '') {
            redirect('/verify-email/resend');
        }

        View::render('auth/registration-pending', [
            'email' => $email,
            'emailSent' => (bool) ($_SESSION['_pending_verification_sent'] ?? false),
            'success' => flash('success'),
            'error' => flash('error'),
        ], 'auth');
    }

    public function showResend(): void
    {
        View::render('auth/resend-verification', [
            'email' => (string) ($_SESSION['_pending_verification_email'] ?? ''),
            'success' => flash('success'),
            'error' => flash('error'),
        ], 'auth');
    }

    public function resend(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/verify-email/resend');
        }

        $email = text_lower(trim((string) ($_POST['email'] ?? '')));
        $service = new EmailVerificationService();
        $record = filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $service->findPendingByEmail($email)
            : null;

        if ($record !== null) {
            try {
                $token = $service->issue((int) $record['restaurant_id'], (int) $record['user_id']);
                $this->sendVerificationEmail([
                    'admin_name' => $record['name'],
                    'admin_email' => $record['email'],
                    'language' => $record['language'],
                ], $token);
            } catch (Throwable $exception) {
                error_log('Verification resend error: ' . $exception->getMessage());
            }
        }

        $_SESSION['_pending_verification_email'] = $email;
        flash('success', trans('registration.resend_generic'));
        redirect('/verify-email/resend');
    }

    public function verify(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $record = (new EmailVerificationService())->verify($token);

        if ($record === null) {
            View::render('auth/verification-result', [
                'verified' => false,
            ], 'auth');
            return;
        }

        flash('success', trans('registration.verified'));
        redirect('/login');
    }

    private function sanitize(array $input): array
    {
        $countryCode = strtoupper(trim((string) ($input['country_code'] ?? '')));
        $countries = $this->countries();
        $timezone = $countries[$countryCode]['timezone'] ?? 'Europe/Lisbon';

        return [
            'legal_name' => trim((string) ($input['legal_name'] ?? '')),
            'trade_name' => trim((string) ($input['trade_name'] ?? '')),
            'country_code' => $countryCode,
            'company_registration_number' => strtoupper(trim((string) ($input['company_registration_number'] ?? ''))),
            'vat_number' => strtoupper(preg_replace('/\s+/', '', (string) ($input['vat_number'] ?? '')) ?? ''),
            'business_type' => trim((string) ($input['business_type'] ?? '')),
            'address_line1' => trim((string) ($input['address_line1'] ?? '')),
            'address_line2' => trim((string) ($input['address_line2'] ?? '')),
            'postal_code' => trim((string) ($input['postal_code'] ?? '')),
            'city' => trim((string) ($input['city'] ?? '')),
            'timezone' => $timezone,
            'admin_name' => trim((string) ($input['admin_name'] ?? '')),
            'admin_email' => text_lower(trim((string) ($input['admin_email'] ?? ''))),
            'password' => (string) ($input['password'] ?? ''),
            'password_confirmation' => (string) ($input['password_confirmation'] ?? ''),
            'language' => in_array(($input['language'] ?? ''), ['pt', 'en'], true)
                ? (string) $input['language']
                : 'pt',
            'terms_accepted' => isset($input['terms_accepted']),
            'privacy_acknowledged' => isset($input['privacy_acknowledged']),
            'marketing_consent' => isset($input['marketing_consent']),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        $countries = $this->countries();
        $businessTypes = ['restaurant', 'cafe', 'bar', 'hotel', 'other'];

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

        if (!in_array($data['business_type'], $businessTypes, true)) {
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

        if (text_length($data['admin_name']) < 2 || text_length($data['admin_name']) > 120) {
            $errors['admin_name'] = trans('registration.validation.admin_name');
        }

        if (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL) || text_length($data['admin_email']) > 190) {
            $errors['admin_email'] = trans('registration.validation.email');
        }

        if (strlen($data['password']) < 8 || strlen($data['password']) > 72) {
            $errors['password'] = trans('registration.validation.password');
        }

        if ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = trans('registration.validation.password_confirmation');
        }

        if (!$data['terms_accepted']) {
            $errors['terms_accepted'] = trans('registration.validation.terms');
        }

        if (!$data['privacy_acknowledged']) {
            $errors['privacy_acknowledged'] = trans('registration.validation.privacy');
        }

        return $errors;
    }

    private function countries(): array
    {
        $countries = require dirname(__DIR__, 2) . '/config/europe.php';
        $language = Locale::current();

        uasort($countries, static fn (array $a, array $b): int => strcmp($a[$language], $b[$language]));

        return $countries;
    }

    private function defaults(): array
    {
        return [
            'legal_name' => '',
            'trade_name' => '',
            'country_code' => 'PT',
            'company_registration_number' => '',
            'vat_number' => '',
            'business_type' => 'restaurant',
            'address_line1' => '',
            'address_line2' => '',
            'postal_code' => '',
            'city' => '',
            'admin_name' => '',
            'admin_email' => '',
            'language' => Locale::current(),
            'terms_accepted' => false,
            'privacy_acknowledged' => false,
            'marketing_consent' => false,
        ];
    }

    private function sendVerificationEmail(array $data, string $token): bool
    {
        $language = $data['language'] === 'en' ? 'en' : 'pt';
        $link = absolute_url('/verify-email?token=' . urlencode($token));

        if ($language === 'en') {
            $subject = 'Confirm your TipsForMe account';
            $title = 'Confirm your business email';
            $message = 'Your company account was created. Confirm this email within 24 hours to activate it and complete the initial setup.';
            $button = 'Confirm email';
            $footer = 'If you did not create this account, ignore this message.';
        } else {
            $subject = 'Confirme a sua conta TipsForMe';
            $title = 'Confirme o e-mail da empresa';
            $message = 'A conta da empresa foi criada. Confirme este e-mail em até 24 horas para ativá-la e concluir a configuração inicial.';
            $button = 'Confirmar e-mail';
            $footer = 'Se não criou esta conta, ignore esta mensagem.';
        }

        try {
            (new SmtpMailer())->send(
                $data['admin_email'],
                $data['admin_name'],
                $subject,
                email_template($title, $data['admin_name'], $message, $button, $link, $footer)
            );
            return true;
        } catch (Throwable $exception) {
            error_log('Verification email error: ' . $exception->getMessage());
            return false;
        }
    }

    private function termsVersion(): string
    {
        return (string) env('LEGAL_TERMS_VERSION', '2026-07-21');
    }

    private function privacyVersion(): string
    {
        return (string) env('LEGAL_PRIVACY_VERSION', '2026-07-21');
    }
}
