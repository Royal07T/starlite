<?php

namespace App\Domain\Shared;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Timezone helpers previously living in app/Helpers/helpers.php.
 * Preserves exact behaviour of parseToUTC/parseToUserTz/getUserTimezone.
 */
final class TimeZone
{
    public static function user(?User $user = null): ?string
    {
        if (empty($user)) {
            $user = Auth::user();
        }

        $tz = $user?->getKey() ? Cache::rememberForever('userTimeZone_'.$user->getKey(), function () use ($user) {
            return current($user->accountSetting()?->where('meta_key', 'timezone')->pluck('meta_value')->first() ?? []);
        }) : null;

        if ($tz) {
            return $tz;
        }

        return setting('_general.timezone') ?? config('app.timezone');
    }

    public static function toUTC(mixed $date): Carbon
    {
        return Carbon::parse($date, self::user())->setTimezone('UTC');
    }

    public static function toUser(mixed $date, ?string $timeZone = null): Carbon
    {
        $tz = $timeZone ?? self::user();

        return Carbon::parse($date, 'UTC')->setTimezone($tz);
    }
}
