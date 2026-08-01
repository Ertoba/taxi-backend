<?php

namespace Tests\Unit;

use App\Services\CommissionCalculator;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    public function test_it_preserves_tetri_in_commission_allocations(): void
    {
        $result = (new CommissionCalculator)->calculate(25.50, 10);

        $this->assertSame(2.55, $result['admin_commission']);
        $this->assertSame(22.95, $result['vendor_commission']);
        $this->assertSame(
            25.50,
            round($result['admin_commission'] + $result['vendor_commission'], 2)
        );
    }

    public function test_it_clamps_invalid_percentage_values(): void
    {
        $this->assertSame(
            ['admin_commission' => 10.00, 'vendor_commission' => 0.00],
            (new CommissionCalculator)->calculate(10, 150)
        );
        $this->assertSame(
            ['admin_commission' => 0.00, 'vendor_commission' => 10.00],
            (new CommissionCalculator)->calculate(10, -5)
        );
    }
}
