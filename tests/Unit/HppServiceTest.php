<?php

namespace Tests\Unit;

use App\Services\HppService;
use PHPUnit\Framework\TestCase;

class HppServiceTest extends TestCase
{
    public function test_calculate_margin_nominal(): void
    {
        $price = 25000;
        $hpp = 15000;
        $margin = HppService::calculateMarginNominal($price, $hpp);
        $this->assertEquals(10000, $margin);
    }

    public function test_calculate_margin_percent(): void
    {
        $price = 20000;
        $hpp = 14000; // margin 6000 -> (6000/20000)*100 = 30%
        $percent = HppService::calculateMarginPercent($price, $hpp);
        $this->assertEquals(30.00, $percent);

        // Price <= 0 returns 0
        $this->assertEquals(0.00, HppService::calculateMarginPercent(0, 10000));
    }

    public function test_calculate_theoretical_price(): void
    {
        // HPP 7000 with 30% target margin -> 7000 / (1 - 0.3) = 10000
        $theoretical = HppService::calculateTheoreticalPrice(7000, 30);
        $this->assertEquals(10000.0, $theoretical);
    }

    public function test_calculate_theoretical_price_invalid_margin_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HppService::calculateTheoreticalPrice(10000, 100);
    }

    public function test_get_margin_status(): void
    {
        $this->assertEquals('Di atas target margin', HppService::getMarginStatus(35, 30));
        $this->assertEquals('Sesuai target', HppService::getMarginStatus(30, 30));
        $this->assertEquals('Di bawah target margin', HppService::getMarginStatus(25, 30));
    }

    public function test_get_suggested_price_with_various_roundings(): void
    {
        // 9420 rounded to 100 -> 9500
        $this->assertEquals(9500, HppService::getSuggestedPrice(9420, 100));

        // 9420 rounded to 500 -> 9500
        $this->assertEquals(9500, HppService::getSuggestedPrice(9420, 500));

        // 9100 rounded to 500 -> 9500
        $this->assertEquals(9500, HppService::getSuggestedPrice(9100, 500));

        // 9420 rounded to 1000 -> 10000
        $this->assertEquals(10000, HppService::getSuggestedPrice(9420, 1000));

        // Exactly on round boundary stays identical
        $this->assertEquals(10000, HppService::getSuggestedPrice(10000, 1000));
    }
}
