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

    public function test_split_details_include_main_branch_and_driver_iban_once(): void
    {
        $details = (new KeepzSplitService)->splitDetails(
            [
                'type' => 'BRANCH',
                'identifier' => '90434fa9-46df-4c44-a4d1-da742ac815da',
            ],
            [
                'type' => 'IBAN',
                'identifier' => 'GE29NB0000000101904917',
            ],
            [
                'platform' => 20.00,
                'driver' => 80.00,
            ]
        );

        $this->assertSame('BRANCH', $details[0]['receiverType']);
        $this->assertSame('90434fa9-46df-4c44-a4d1-da742ac815da', $details[0]['receiverIdentifier']);
        $this->assertSame(20.00, $details[0]['amount']);
        $this->assertSame('IBAN', $details[1]['receiverType']);
        $this->assertSame('GE29NB0000000101904917', $details[1]['receiverIdentifier']);
        $this->assertSame(80.00, $details[1]['amount']);
    }

    public function test_it_rejects_missing_commission_data(): void
    {
        $booking = new Booking;
        $booking->vendor_commission = 0;
        $booking->admin_commission = 0;

        $this->expectException(RuntimeException::class);
        (new KeepzSplitService)->allocation($booking, 100);
    }

    public function test_it_rejects_zero_platform_share(): void
    {
        $booking = new Booking;
        $booking->vendor_commission = 100;
        $booking->admin_commission = 0;

        $this->expectException(RuntimeException::class);
        (new KeepzSplitService)->allocation($booking, 100);
    }
}
