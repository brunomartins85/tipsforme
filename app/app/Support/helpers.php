<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Locale;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return ($value === false || $value === null || $value === '') ? $default : $value;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}



if (!function_exists('slugify')) {
    function slugify(string $value): string
    {
        $value = trim($value);
        $transliterated = function_exists('iconv')
            ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value)
            : $value;

        if (is_string($transliterated) && $transliterated !== '') {
            $value = $transliterated;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }
}

if (!function_exists('text_lower')) {
    function text_lower(string $value): string
    {
        return function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);
    }
}

if (!function_exists('text_length')) {
    function text_length(string $value): int
    {
        return function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : strlen($value);
    }
}

if (!function_exists('text_initial')) {
    function text_initial(string $value): string
    {
        $value = trim($value);
        $initial = function_exists('mb_substr')
            ? mb_substr($value !== '' ? $value : 'U', 0, 1, 'UTF-8')
            : substr($value !== '' ? $value : 'U', 0, 1);

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($initial, 'UTF-8')
            : strtoupper($initial);
    }
}




if (!function_exists('app_version')) {
    function app_version(): string
    {
        return (string) env('APP_VERSION', '1.1.0');
    }
}

if (!function_exists('client_ip')) {
    function client_ip(): string
    {
        // Usa o endereço fornecido diretamente pelo servidor para evitar cabeçalhos falsificados.
        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
    }
}

if (!function_exists('trans')) {
    function trans(string $key, array $replace = []): string
    {
        return Locale::get($key, $replace);
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $appUrl = (string) env('APP_URL', '');
        $path = parse_url($appUrl, PHP_URL_PATH);

        if (!is_string($path) || $path === '/' || $path === '') {
            return '';
        }

        return '/' . trim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        $base = app_base_path();
        $normalizedPath = '/' . ltrim($path, '/');

        if ($normalizedPath === '/') {
            return $base !== '' ? $base . '/' : '/';
        }

        return $base . $normalizedPath;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('/assets/' . ltrim($path, '/'));
    }
}



if (!function_exists('absolute_url')) {
    function absolute_url(string $path = '/'): string
    {
        $base = rtrim((string) env('APP_URL', ''), '/');
        $normalizedPath = '/' . ltrim($path, '/');

        return $base . ($normalizedPath === '/' ? '' : $normalizedPath);
    }
}

if (!function_exists('email_template')) {
    function email_template(
        string $title,
        string $name,
        string $message,
        string $button,
        string $link,
        string $footer
    ): string {
        $safeTitle = e($title);
        $safeName = e($name);
        $safeMessage = e($message);
        $safeButton = e($button);
        $safeLink = e($link);
        $safeFooter = e($footer);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#0b1c30;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px;background:#f4f7fb;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e0e7ef;">
<tr><td style="padding:28px 32px;background:#0b1c30;color:#ffffff;font-size:24px;font-weight:700;">tipsforme</td></tr>
<tr><td style="padding:32px;">
<h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">{$safeTitle}</h1>
<p style="margin:0 0 12px;line-height:1.6;">Olá / Hello, {$safeName}.</p>
<p style="margin:0 0 24px;line-height:1.6;color:#4f5d6d;">{$safeMessage}</p>
<p style="margin:0 0 24px;"><a href="{$safeLink}" style="display:inline-block;padding:14px 20px;border-radius:10px;background:#006c49;color:#ffffff;text-decoration:none;font-weight:700;">{$safeButton}</a></p>
<p style="margin:0 0 8px;color:#6a7480;font-size:13px;line-height:1.5;">{$safeFooter}</p>
<p style="margin:0;color:#6a7480;font-size:12px;word-break:break-all;">{$safeLink}</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}

if (!function_exists('current_path')) {
    function current_path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = app_base_path();

        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $path = '/' . ltrim($path, '/');

        return $path !== '/' ? rtrim($path, '/') : '/';
    }
}

if (!function_exists('route_is')) {
    function route_is(string $prefix): bool
    {
        $path = current_path();
        $prefix = '/' . trim($prefix, '/');

        return $path === $prefix || str_starts_with($path, $prefix . '/');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        $stored = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $stored;
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return $_SESSION['auth_user'] ?? null;
    }
}

if (!function_exists('format_date')) {
    function format_date(string $date): string
    {
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return $date;
        }

        return date('d/m/Y', $timestamp);
    }
}



if (!function_exists('format_datetime')) {
    function format_datetime(string $value): string
    {
        $timestamp = strtotime($value);

        return $timestamp === false ? $value : date('d/m/Y H:i', $timestamp);
    }
}

if (!function_exists('format_currency')) {
    function format_currency(string|float|int|null $value, string $currency = 'EUR'): string
    {
        $amount = (float) ($value ?? 0);
        $formatted = number_format($amount, 2, ',', '.');

        return $currency === 'EUR' ? '€ ' . $formatted : $formatted . ' ' . $currency;
    }
}

if (!function_exists('money_input')) {
    function money_input(string|float|int|null $value): string
    {
        return number_format((float) ($value ?? 0), 2, ',', '');
    }
}

if (!function_exists('format_percentage')) {
    function format_percentage(string|float|int|null $value): string
    {
        return number_format((float) ($value ?? 0), 2, ',', '.') . '%';
    }
}

if (!function_exists('format_month')) {
    function format_month(string $value): string
    {
        $timestamp = strtotime(strlen($value) === 7 ? $value . '-01' : $value);

        if ($timestamp === false) {
            return $value;
        }

        $month = date('m', $timestamp);
        $year = date('Y', $timestamp);

        return trans('months.' . $month) . ' ' . $year;
    }
}

if (!function_exists('format_cents')) {
    function format_cents(int $cents): string
    {
        return format_currency(number_format($cents / 100, 2, '.', ''));
    }
}

if (!function_exists('nav_icon')) {
    function nav_icon(string $name): string
    {
        // Ícones internos em SVG: não dependem de bibliotecas externas.
        $paths = [
            'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
            'employees' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'shifts' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'entries' => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18"/><path d="M7 15h2"/>',
            'settlements' => '<path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2Z"/><path d="M9 7h6M9 11h6M9 15h3"/>',
            'reports' => '<path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/>',
            'audit' => '<path d="M12 3 4.5 6v5.5c0 4.6 3.2 7.8 7.5 9.5 4.3-1.7 7.5-4.9 7.5-9.5V6L12 3Z"/><path d="M9 12h6M12 9v6"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.09A1.7 1.7 0 0 0 8.97 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3.09 14H3v-4h.09A1.7 1.7 0 0 0 4.6 8.97a1.7 1.7 0 0 0-.34-1.88l-.06-.06L7.03 4.2l.06.06A1.7 1.7 0 0 0 8.97 4.6 1.7 1.7 0 0 0 10 3.09V3h4v.09a1.7 1.7 0 0 0 1.03 1.51 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06a1.7 1.7 0 0 0-.34 1.88A1.7 1.7 0 0 0 20.91 10H21v4h-.09A1.7 1.7 0 0 0 19.4 15Z"/>',
            'wallet' => '<path d="M20 7V5a2 2 0 0 0-2-2H5a3 3 0 0 0 0 6h15v10H5a3 3 0 0 1-3-3V6"/><path d="M16 13h2"/>',
            'statement' => '<path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
            'check' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
            'support' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
        ];

        $path = $paths[$name] ?? $paths['dashboard'];

        return '<svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
    }
}

if (!function_exists('current_section_label')) {
    function current_section_label(): string
    {
        return match (true) {
            route_is('/employees') => trans('nav.employees'),
            route_is('/shifts') => trans('nav.operations'),
            route_is('/entries') => trans('nav.operations'),
            route_is('/settlements') => trans('nav.payments'),
            route_is('/reports') => trans('nav.reports'),
            route_is('/audit') => trans('nav.audit'),
            route_is('/settings') => trans('nav.settings'),
            route_is('/support-project') => trans('nav.support_project'),
            route_is('/legal') => trans('legal.eyebrow'),
            route_is('/data-rights/request') => trans('data_request.title'),
            route_is('/onboarding') => trans('onboarding.title'),
            route_is('/my/statement') => trans('employee.nav.statement'),
            route_is('/my/payments') => trans('employee.nav.payments'),
            route_is('/my/dashboard') => trans('employee.nav.balance'),
            default => trans('nav.dashboard'),
        };
    }
}
