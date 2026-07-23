<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Services\KeepzSplitService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class KeepzSplitServiceTest extends TestCase
{
    public function test_allocation_exactly_matches_the_customer_total(): void
    {
        $booking = new Booking;
        $booking->vendor_commission = 75.25;

        $allocation = (new KeepzSplitService)->allocation($booking, 100.00);

        $this->assertSame(100.00, $allocation['total']);
        $this->assertSame(24.75, $allocation['platform']);
        $this->assertSame(75.25, $allocation['driver']);
        $this->assertSame(
            $allocation['total'],
            round($allocation['platform'] + $allocation['driver'], 2)
        );
    }

    public function test_it_rejects_a_driver_share_above_the_payment_total(): void
    {
        $booking = new Booking;
        $booking->vendor_commission = 101;

        $this->expectException(RuntimeException::class);
        (new KeepzSplitService)->allocation($booking, 100);
    }
}
