<?php

namespace Tests\Feature;

use App\Domain\Shared\SettingReader;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BookingSettingReaderCharacterizationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_complete_booking_after_days_fallbacks_to_three_when_unset(): void
    {
        $this->assertSame(3, SettingReader::completeBookingAfterDays());
    }

    public function test_reserved_time_minutes_fallbacks_to_thirty_when_unset(): void
    {
        $this->assertSame(30, SettingReader::reservedTimeMinutes());
    }

    public function test_tutor_sessions_allowed_fallbacks_to_false_when_unset(): void
    {
        $this->assertFalse(SettingReader::subscriptionSessionsAllowedForTutor());
    }
}
