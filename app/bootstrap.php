<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\Locale;
use App\Core\SecurityHeaders;

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/app/Support/helpers.php';

Env::load(__DIR__ . '/.env');

date_default_timezone_set(env('APP_TIMEZONE', 'Europe/Lisbon'));

SecurityHeaders::send();

// Endurece o comportamento da sessão sem depender do php.ini do alojamento.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');

$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

session_name(env('SESSION_NAME', 'tipsforme_session'));
session_set_cookie_params([
    'lifetime' => 0,
    'path' => app_base_path() !== '' ? app_base_path() : '/',
    'domain' => '',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Após o login, cada restaurante pode operar no próprio fuso horário.
$restaurantTimezone = $_SESSION['auth_user']['restaurant_timezone'] ?? null;
if (is_string($restaurantTimezone) && in_array($restaurantTimezone, timezone_identifiers_list(), true)) {
    date_default_timezone_set($restaurantTimezone);
}

Locale::boot();
