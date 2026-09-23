<?php

namespace App\Domain\Shared;

use Illuminate\Support\Str;

/**
 * Identifier generation. Call sites currently use Str::uuid() directly;
 * this class is the canonical place for future identifier schemes.
 */
final class Guid
{
    public static function generate(): string
    {
        return (string) Str::uuid();
    }

    public static function slug(?string $value): string
    {
        return Str::slug((string) $value);
    }

    public static function random(int $length = 16): string
    {
        return Str::random($length);
    }

    public static function orderedUuid(): string
    {
        return Str::orderedUuid();
    }
}
