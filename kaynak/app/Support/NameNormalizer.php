<?php

namespace App\Support;

use Illuminate\Support\Str;

final class NameNormalizer
{
    public static function normalize(null|string $value): string
    {
        $normalized = Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->trim()
            ->value();

        return preg_replace('/\s+/', ' ', $normalized) ?: '';
    }
}
