<?php

namespace App\Services;

class CommissionCalculator
{
    /**
     * Keep fare allocations at currency precision and guarantee that the
     * platform and driver portions add back up to the authoritative fare.
     */
    public function calculate(float $basePrice, float $adminPercent): array
    {
        $basePrice = round(max(0, $basePrice), 2);
        $adminPercent = min(100, max(0, $adminPercent));
        $adminCommission = round($basePrice * $adminPercent / 100, 2);
        $vendorCommission = round($basePrice - $adminCommission, 2);

        return [
            'admin_commission' => $adminCommission,
            'vendor_commission' => $vendorCommission,
        ];
    }
}
