<?php

namespace Tests\Unit;

use App\Services\Pricing;
use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class PricingTest extends TestCase
{
    public function test_cod_menambah_ongkir_per_km(): void
    {
        // 3 galon x 6.000 + 2 km x 1.000 = 20.000
        $this->assertSame(
            ['gallon_total' => 18000, 'distance_km' => 2, 'delivery_fee' => 2000, 'total' => 20000],
            Pricing::compute(3, 'cod', 2, 6000, 1000)
        );
    }

    public function test_ambil_di_tempat_tidak_kena_ongkir(): void
    {
        $result = Pricing::compute(2, 'pickup', 4, 6000, 1000);

        $this->assertSame(0, $result['delivery_fee']);
        $this->assertSame(12000, $result['total']);
    }

    public function test_pilihan_jarak_dirapikan(): void
    {
        $this->assertSame([2, 3, 4], Pricing::parseDistances(' 4 , 2,2, x, 0, 3'));
        $this->assertSame([1], Pricing::parseDistances(''));
    }

    public function test_nomor_whatsapp_dinormalisasi(): void
    {
        $this->assertSame('6281234567890', Phone::normalize('0812-3456-7890'));
        $this->assertSame('6281234567890', Phone::normalize('+62 812 3456 7890'));
        $this->assertSame('', Phone::normalize(null));
        $this->assertSame('081234567890', Phone::local('6281234567890'));
    }
}
