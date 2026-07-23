<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\AppUserMeta;
use App\Models\Booking;
use App\Models\GeneralSetting;
use App\Support\KeepzReceiver;
use RuntimeException;

class KeepzSplitService
{
    public const DRIVER_META_KEY = 'keepz split receiver';

    public function isEnabled(): bool
    {
        return strcasecmp((string) GeneralSetting::getMetaValue('keepz_split_status'), 'Active') === 0;
    }

    public function platformReceiver(string $mode): array
    {
        $prefix = strtolower($mode) === 'live' ? 'live_keepz_' : 'test_keepz_';
        $type = KeepzReceiver::normalizeType(
            GeneralSetting::getMetaValue($prefix.'split_platform_receiver_type')
        );
        $identifier = KeepzReceiver::normalizeIdentifier(
            GeneralSetting::getMetaValue($prefix.'split_platform_receiver_identifier'),
            $type
        );

        if (! KeepzReceiver::isValid($type, $identifier)) {
            throw new RuntimeException('Keepz platform split receiver is not configured correctly.');
        }

        return [
            'type' => $type,
            'identifier' => $identifier,
            'masked_identifier' => KeepzReceiver::mask($type, $identifier),
        ];
    }

    public function driverReceiver(AppUser $driver): array
    {
        $metadata = AppUserMeta::where('user_id', $driver->id)
            ->where('meta_key', self::DRIVER_META_KEY)
            ->first();
        $details = $metadata ? json_decode((string) $metadata->meta_value, true) : null;

        if (! is_array($details) || (int) ($details['is_active'] ?? 0) !== 1) {
            throw new RuntimeException('The driver has no active Keepz split receiver.');
        }

        $type = KeepzReceiver::normalizeType($details['keepz_receiver_type'] ?? null);
        $identifier = KeepzReceiver::normalizeIdentifier(
            $details['keepz_receiver_identifier'] ?? null,
            $type
        );

        if ($type !== KeepzReceiver::TYPE_IBAN || ! KeepzReceiver::isValid($type, $identifier)) {
            throw new RuntimeException('The driver Keepz IBAN is invalid.');
        }

        return [
            'type' => $type,
            'identifier' => $identifier,
            'masked_identifier' => KeepzReceiver::mask($type, $identifier),
            'account_name' => trim((string) ($details['account_name'] ?? '')),
        ];
    }

    public function allocation(Booking $booking, float $totalAmount): array
    {
        $total = round($totalAmount, 2);
        $driverCommissionBase = round((float) $booking->vendor_commission, 2);
        $platformCommissionBase = round((float) $booking->admin_commission, 2);
        $commissionBase = round($driverCommissionBase + $platformCommissionBase, 2);

        if ($total <= 0 || $driverCommissionBase <= 0 || $platformCommissionBase < 0 || $commissionBase <= 0) {
            throw new RuntimeException('Keepz split amounts are invalid for this ride.');
        }

        $driverRatio = $driverCommissionBase / $commissionBase;
        if ($driverRatio <= 0 || $driverRatio > 1) {
            throw new RuntimeException('Keepz split commission ratio is invalid for this ride.');
        }

        $driverAmount = round($total * $driverRatio, 2);
        $platformAmount = round($total - $driverAmount, 2);

        if ($driverAmount <= 0 || $platformAmount < 0) {
            throw new RuntimeException('Keepz split amounts are invalid for this ride.');
        }

        if (abs(($platformAmount + $driverAmount) - $total) > 0.001) {
            throw new RuntimeException('Keepz split amounts do not equal the ride payment total.');
        }

        return [
            'total' => $total,
            'platform' => $platformAmount,
            'driver' => $driverAmount,
            'driver_ratio' => round($driverRatio, 8),
        ];
    }

    public function splitDetails(array $platformReceiver, array $driverReceiver, array $allocation): array
    {
        return [
            [
                'receiverType' => $platformReceiver['type'],
                'receiverIdentifier' => $platformReceiver['identifier'],
                'amount' => $allocation['platform'],
            ],
            [
                'receiverType' => $driverReceiver['type'],
                'receiverIdentifier' => $driverReceiver['identifier'],
                'amount' => $allocation['driver'],
            ],
        ];
    }
}
