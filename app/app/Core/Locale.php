<?php

declare(strict_types=1);

namespace App\Core;

final class Locale
{
    private const AVAILABLE = ['pt', 'en'];
    private static array $translations = [];

    public static function boot(): void
    {
        $requested = $_GET['lang'] ?? null;

        if (is_string($requested) && in_array($requested, self::AVAILABLE, true)) {
            $_SESSION['language'] = $requested;
        }

        $language = $_SESSION['language'] ?? env('APP_LANGUAGE', 'pt');

        if (!in_array($language, self::AVAILABLE, true)) {
            $language = 'pt';
        }

        $_SESSION['language'] = $language;
        self::$translations = require dirname(__DIR__, 2) . '/resources/lang/' . $language . '.php';
    }

    public static function current(): string
    {
        return $_SESSION['language'] ?? 'pt';
    }

    public static function get(string $key, array $replace = []): string
    {
        $value = self::$translations[$key] ?? $key;

        foreach ($replace as $placeholder => $replacement) {
            $value = str_replace(':' . $placeholder, (string) $replacement, $value);
        }

        return $value;
    }
}
