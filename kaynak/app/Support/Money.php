<?php

namespace App\Support;

final class Money
{
    public static function normalize(null|int|float|string $value): string
    {
        $value = $value ?? '0';

        if (is_int($value) || is_float($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        $normalized = trim((string) $value);
        $normalized = str_replace([' ', ','], ['', '.'], $normalized);

        if ($normalized === '' || $normalized === '.') {
            return '0.00';
        }

        return bcadd($normalized, '0', 2);
    }

    public static function add(null|int|float|string $left, null|int|float|string $right): string
    {
        return bcadd(static::normalize($left), static::normalize($right), 2);
    }

    public static function sub(null|int|float|string $left, null|int|float|string $right): string
    {
        return bcsub(static::normalize($left), static::normalize($right), 2);
    }

    public static function mul(null|int|float|string $left, null|int|float|string $right, int $scale = 4): string
    {
        return bcmul(static::normalize($left), static::normalize($right), $scale);
    }

    public static function div(null|int|float|string $left, null|int|float|string $right, int $scale = 4): string
    {
        if (static::cmp($right, '0') === 0) {
            return '0.00';
        }

        return bcdiv(static::normalize($left), static::normalize($right), $scale);
    }

    public static function cmp(null|int|float|string $left, null|int|float|string $right): int
    {
        return bccomp(static::normalize($left), static::normalize($right), 2);
    }

    public static function min(null|int|float|string $left, null|int|float|string $right): string
    {
        return static::cmp($left, $right) <= 0 ? static::normalize($left) : static::normalize($right);
    }

    public static function max(null|int|float|string $left, null|int|float|string $right): string
    {
        return static::cmp($left, $right) >= 0 ? static::normalize($left) : static::normalize($right);
    }

    public static function float(null|int|float|string $value): float
    {
        return (float) static::normalize($value);
    }

    public static function isPositive(null|int|float|string $value): bool
    {
        return static::cmp($value, '0') === 1;
    }
}
