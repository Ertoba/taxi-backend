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
        $booking->admin_commission = 24.75;

        $allocation = (new KeepzSplitService)->allocation($booking, 100.00);

        $this->assertSame(100.00, $allocation['total']);
        $this->assertSame(24.75, $allocation['platform']);
        $this->assertSame(75.25, $allocation['driver']);
        $this->assertSame(
            $allocation['total'],
            round($allocation['platform'] + $allocation['driver'], 2)
        );
    }

    public function test_partial_card_amount_uses_the_original_commission_ratio(): void
    {
        $booking = new Booking;
        $booking->vendor_commission = 80;
        $booking->admin_commission = 20;

        $allocation = (new KeepzSplitService)->allocation($booking, 25);

        $this->assertSame(20.00, $allocation['driver']);
        $this->assertSame(5.00, $allocation['platform']);
        $this->assertSame(25.00, $allocation['total']);
    }

    public function test_it_rejects_missing_commission_data(): void
    {
        $booking = new Booking;
        $booking->vendor_commission = 0;
        $booking->admin_commission = 0;

        $this->expectException(RuntimeException::class);
        (new KeepzSplitService)->allocation($booking, 100);
    }
}
