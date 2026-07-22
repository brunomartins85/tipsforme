<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Locale;
use App\Core\View;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PasswordResetService;
use App\Services\SmtpMailer;
use Throwable;

final class PasswordController
{
    public function showForgot(): void
    {
        View::render('auth/forgot-password', [
            'success' => flash('success'),
            'error' => flash('error'),
            'email' => $_SESSION['_old_email'] ?? '',
        ], 'auth');

        unset($_SESSION['_old_email']);
    }

    public function sendReset(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/forgot-password');
        }

        $email = text_lower(trim((string) ($_POST['email'] ?? '')));
        $_SESSION['_old_email'] = $email;

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user = (new User())->findByEmail($email);

            if ($user !== null && (int) ($user['password_reset_enabled'] ?? 1) === 1) {
                try {
                    $token = (new PasswordResetService())->issue(
                        (int) $user['restaurant_id'],
                        (int) $user['id'],
                        'password_reset'
                    );
                    $link = absolute_url('/reset-password?token=' . urlencode($token));
                    (new SmtpMailer())->send(
                        $user['email'],
                        $user['name'],
                        $user['language'] === 'en' ? 'Reset your TipsForMe password' : 'Redefina sua senha do TipsForMe',
                        $this->resetEmail($user, $link)
                    );
                } catch (Throwable $exception) {
                    error_log($exception->getMessage());
                }
            }
        }

        unset($_SESSION['_old_email']);
        flash('success', trans('password.email_sent'));
        redirect('/forgot-password');
    }

    public function showReset(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $record = (new PasswordResetService())->findValid($token);

        if ($record !== null && in_array($record['language'], ['pt', 'en'], true)) {
            $_SESSION['language'] = $record['language'];
            Locale::boot();
        }

        View::render('auth/reset-password', [
            'token' => $token,
            'record' => $record,
            'errors' => [],
        ], 'auth');
    }

    public function reset(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/login');
        }

        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');
        $service = new PasswordResetService();
        $record = $service->findValid($token);
        $errors = [];

        if ($record === null) {
            $errors['token'] = trans('password.invalid_token');
        }

        if (strlen($password) < 8 || strlen($password) > 72) {
            $errors['password'] = trans('password.validation');
        }

        if ($password !== $confirmation) {
            $errors['password_confirmation'] = trans('password.confirmation');
        }

        if ($errors !== []) {
            http_response_code(422);
            View::render('auth/reset-password', [
                'token' => $token,
                'record' => $record,
                'errors' => $errors,
            ], 'auth');
            return;
        }

        if (!$service->consume($token, password_hash($password, PASSWORD_DEFAULT))) {
            flash('error', trans('password.invalid_token'));
            redirect('/login');
        }

        AuditLogger::recordFor(
            (int) $record['restaurant_id'],
            (int) $record['user_id'],
            'password.reset',
            'user',
            (int) $record['user_id'],
            null,
            ['purpose' => $record['purpose']]
        );
        flash('success', trans('password.updated'));
        redirect('/login');
    }

    private function resetEmail(array $user, string $link): string
    {
        $english = $user['language'] === 'en';
        $title = $english ? 'Reset your password' : 'Redefina sua senha';
        $message = $english
            ? 'We received a request to reset your TipsForMe password. This link expires in 60 minutes.'
            : 'Recebemos um pedido para redefinir sua senha do TipsForMe. Este link expira em 60 minutos.';
        $button = $english ? 'Choose a new password' : 'Escolher nova senha';
        $ignore = $english
            ? 'If you did not request this, ignore this email.'
            : 'Caso não tenha solicitado, ignore este e-mail.';

        return email_template($title, $user['name'], $message, $button, $link, $ignore);
    }
}
