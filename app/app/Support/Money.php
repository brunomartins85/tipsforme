<?php

declare(strict_types=1);

namespace App\Support;

final class Money
{
    public static function parseToCents(string $input): ?int
    {
        $value = trim($input);

        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9,.-]/u', '', $value) ?? '';

        if ($value === '' || str_contains($value, '-')) {
            return null;
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');
        $decimalPosition = false;

        if ($lastComma !== false && $lastDot !== false) {
            $decimalPosition = max($lastComma, $lastDot);
        } elseif ($lastComma !== false) {
            $digitsAfter = strlen($value) - $lastComma - 1;
            $decimalPosition = $digitsAfter <= 2 ? $lastComma : false;
        } elseif ($lastDot !== false) {
            $digitsAfter = strlen($value) - $lastDot - 1;
            $decimalPosition = $digitsAfter <= 2 ? $lastDot : false;
        }

        if ($decimalPosition === false) {
            $integerDigits = preg_replace('/\D/', '', $value) ?? '';

            return $integerDigits === '' ? null : ((int) $integerDigits * 100);
        }

        $integerPart = substr($value, 0, $decimalPosition);
        $decimalPart = substr($value, $decimalPosition + 1);
        $integerDigits = preg_replace('/\D/', '', $integerPart) ?? '';
        $decimalDigits = preg_replace('/\D/', '', $decimalPart) ?? '';

        if ($integerDigits === '') {
            $integerDigits = '0';
        }

        if ($decimalDigits === '' || strlen($decimalDigits) > 2) {
            return null;
        }

        $decimalDigits = str_pad($decimalDigits, 2, '0');

        return ((int) $integerDigits * 100) + (int) $decimalDigits;
    }

    public static function toDatabase(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    public static function databaseToCents(string|float|int|null $value): int
    {
        $normalized = number_format((float) ($value ?? 0), 2, '.', '');
        [$integer, $decimal] = array_pad(explode('.', $normalized, 2), 2, '00');

        return ((int) $integer * 100) + (int) str_pad($decimal, 2, '0');
    }

    public static function feeInCents(int $grossCents, string|float|int $percentage): int
    {
        $basisPoints = (int) round((float) $percentage * 100);

        return (int) round(($grossCents * $basisPoints) / 10000);
    }

    /**
     * Divide centavos de forma exata. Os centavos restantes são entregues
     * aos primeiros colaboradores, ordenados pelo ID.
     */
    public static function splitCents(int $cents, array $employeeIds): array
    {
        $employeeIds = array_values(array_unique(array_map('intval', $employeeIds)));
        sort($employeeIds, SORT_NUMERIC);

        if ($employeeIds === []) {
            return [];
        }

        $count = count($employeeIds);
        $base = intdiv($cents, $count);
        $remainder = $cents % $count;
        $result = [];

        foreach ($employeeIds as $index => $employeeId) {
            $result[$employeeId] = $base + ($index < $remainder ? 1 : 0);
        }

        return $result;
    }
}
