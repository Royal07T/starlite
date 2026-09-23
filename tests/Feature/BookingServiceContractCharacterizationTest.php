<?php

namespace Tests\Feature;

use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingServiceContractCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private function contractOf(string $method): ?string
    {
        $ref = new \ReflectionMethod(BookingService::class, $method);
        return $ref->hasReturnType() ? (string) $ref->getReturnType() : null;
    }

    #[Test]
    public function five_money_carriers_exist_named_as_the_tree_spells_them(): void
    {
        $this->assertTrue(method_exists(BookingService::class, 'addUserSubjectGroupSessions'));
        $this->assertTrue(method_exists(BookingService::class, 'addTimeSlots'));
        $this->assertTrue(method_exists(BookingService::class, 'rescheduleSession'));
        $this->assertTrue(method_exists(BookingService::class, 'reservedBookingSlot'));
        $this->assertTrue(method_exists(BookingService::class, 'createBookingEventGoogleCalendar'));
    }

    #[Test]
    public function each_carrier_replays_green_event_intent_must_preserve(): void
    {
        $this->markTestSkipped('contract-replay requires live MySQL substrate; see Phase 3.1 characterization gate');
    }
}
