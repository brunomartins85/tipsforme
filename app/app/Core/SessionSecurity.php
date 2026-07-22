<?php

declare(strict_types=1);

namespace App\Core;

final class SessionSecurity
{
    private const SECURITY_KEY = '_session_security';
    private const REGENERATE_INTERVAL = 900;

    public static function markLogin(): void
    {
        $now = time();
        $_SESSION[self::SECURITY_KEY] = [
            'last_activity' => $now,
            'last_regeneration' => $now,
        ];
    }

    public static function validate(): bool
    {
        if (empty($_SESSION['auth_user'])) {
            return false;
        }

        $now = time();
        $timeout = max(300, (int) env('SESSION_IDLE_TIMEOUT', '1800'));
        $security = $_SESSION[self::SECURITY_KEY] ?? [];
        $lastActivity = (int) ($security['last_activity'] ?? $now);
        $lastRegeneration = (int) ($security['last_regeneration'] ?? $now);

        if (($now - $lastActivity) > $timeout) {
            unset($_SESSION['auth_user'], $_SESSION[self::SECURITY_KEY]);
            session_regenerate_id(true);
            return false;
        }

        if (($now - $lastRegeneration) >= self::REGENERATE_INTERVAL) {
            session_regenerate_id(true);
            $lastRegeneration = $now;
        }

        $_SESSION[self::SECURITY_KEY] = [
            'last_activity' => $now,
            'last_regeneration' => $lastRegeneration,
        ];

        return true;
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parameters['path'],
                $parameters['domain'],
                $parameters['secure'],
                $parameters['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
