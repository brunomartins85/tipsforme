<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'TipsForMe'),
    'version' => env('APP_VERSION', '1.0.1'),
    'url' => env('APP_URL', 'http://localhost'),
    'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'timezone' => env('APP_TIMEZONE', 'Europe/Lisbon'),
    'language' => env('APP_LANGUAGE', 'pt'),
    'session_idle_timeout' => (int) env('SESSION_IDLE_TIMEOUT', '1800'),
    'login_max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', '5'),
    'login_lock_minutes' => (int) env('LOGIN_LOCK_MINUTES', '15'),
];
