<?php

namespace Tests\Unit;

use App\Support\KeepzReceiver;
use PHPUnit\Framework\TestCase;

class KeepzReceiverTest extends TestCase
{
    public function test_it_normalizes_and_validates_a_georgian_iban(): void
    {
        $iban = KeepzReceiver::normalizeIdentifier(
            'ge29 nb00 0000 0101 9049 17',
            KeepzReceiver::TYPE_IBAN
        );

        $this->assertSame('GE29NB0000000101904917', $iban);
        $this->assertTrue(KeepzReceiver::isValid(KeepzReceiver::TYPE_IBAN, $iban));
    }

    public function test_it_rejects_non_georgian_or_malformed_iban_values(): void
    {
        $this->assertFalse(KeepzReceiver::isValid(KeepzReceiver::TYPE_IBAN, 'DE89370400440532013000'));
        $this->assertFalse(KeepzReceiver::isValid(KeepzReceiver::TYPE_IBAN, 'GE00INVALID'));
    }

    public function test_it_validates_uuid_receivers(): void
    {
        $uuid = '0cac1858-9444-43b2-9a64-a65b472de416';

        $this->assertTrue(KeepzReceiver::isValid(KeepzReceiver::TYPE_BRANCH, $uuid));
        $this->assertTrue(KeepzReceiver::isValid(KeepzReceiver::TYPE_USER, $uuid));
        $this->assertFalse(KeepzReceiver::isValid(KeepzReceiver::TYPE_BRANCH, 'not-a-uuid'));
    }

    public function test_it_masks_receiver_identifiers(): void
    {
        $masked = KeepzReceiver::mask(
            KeepzReceiver::TYPE_IBAN,
            'GE29NB0000000101904917'
        );

        $this->assertStringStartsWith('GE29', $masked);
        $this->assertStringEndsWith('4917', $masked);
        $this->assertStringContainsString('*', $masked);
    }
}
