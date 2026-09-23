<?php

namespace App\Domain\Shared;

use RuntimeException;

/**
 * Typed, fail-closed readers for the money-path settings.
 *
 * Domain code must never read raw `setting('_lernen.x')` strings and guess — a
 * mistyped int silently becomes a weird booking window and a missing key lets
 * the money path run with a wrong default. Instead call sites ask
 * `SettingReader::completedAfterDays()` / `::reservedTimeMinutes()` /
 * `::subscriptionsAllowedForTutor()` and get a value that is ALREADY the right
 * type, with a sane default after a cache-miss and a typed guard on save.
 *
 * All three back onto the Laravel settings cache; cache identity is the same
 * key space SettingsUpdatedListener uses, so a settings save invalidates these
 * readers for the next request through the existing listener.
 */
final class SettingReader
{
    private static array $memoized = [];

    public static function reset(): void
    {
        self::$memoized = [];
    }

    /** _lernen.complete_booking_after_days — minutes-to-complete window (default 3d). */
    public static function completeBookingAfterDays(): int
    {
        return self::intSetting('_lernen.complete_booking_after_days', 3);
    }

    /** _lernen.booking_reserved_time — minutes a slot stays reserved pre-payment (default 30). */
    public static function reservedTimeMinutes(): int
    {
        return self::intSetting('_lernen.booking_reserved_time', 30);
    }

    /**
     * _lernen.subscription_sessions_allowed — which money-path gates treat the
     * session bundle's slot as claimable by subscription credits. Only 'tutor'
     * short-circuits the credits check; anything else fails closed (default).
     */
    public static function subscriptionSessionsAllowedForTutor(): bool
    {
        $value = self::stringSetting('_lernen.subscription_sessions_allowed', '');

        return $value === 'tutor';
    }

    private static function intSetting(string $key, int $default): int
    {
        $value = self::raw($key);
        if ($value === null || $value === '') {
            self::remember($key, $default);

            return $default;
        }

        if (! is_numeric($value)) {
            throw new RuntimeException("Money-path setting [{$key}] must be numeric, got non-numeric value.");
        }

        $int = (int) $value;
        self::remember($key, $int);

        return $int;
    }

    private static function stringSetting(string $key, string $default): string
    {
        $value = self::raw($key);
        if ($value === null) {
            self::remember($key, $default);

            return $default;
        }

        self::remember($key, (string) $value);

        return (string) $value;
    }

    private static function raw(string $key): mixed
    {
        if (array_key_exists($key, self::$memoized)) {
            return self::$memoized[$key];
        }

        $value = function_exists('setting') ? setting($key) : null;

        return $value === null ? null : $value;
    }

    private static function remember(string $key, mixed $value): void
    {
        self::$memoized[$key] = $value;
    }
}
