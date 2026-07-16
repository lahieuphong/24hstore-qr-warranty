<?php

namespace Tests\Feature;

use App\Enums\WarrantyStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarrantyLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_qr_lookup_displays_required_fields_but_not_internal_note(): void
    {
        $product = Product::factory()->create([
            'product_code' => 'IP15-128-BLK',
            'name' => 'Điện thoại mẫu 128GB',
            'imei' => '012345678901234',
            'warehouse_date' => '2026-01-15',
            'warranty_months' => 12,
            'warranty_status' => WarrantyStatus::ACTIVE,
            'internal_note' => 'Ghi chú tuyệt mật nội bộ',
        ]);

        $response = $this->get(route('warranty.show', ['product' => $product->qr_token]));

        $response->assertOk()
            ->assertSee('IP15-128-BLK')
            ->assertSee('Điện thoại mẫu 128GB')
            ->assertSee('012345678901234')
            ->assertSee('15/01/2026')
            ->assertSee('12 tháng')
            ->assertSee('15/01/2027')
            ->assertSee('Còn bảo hành')
            ->assertDontSee('Ghi chú tuyệt mật nội bộ')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_unknown_qr_token_returns_not_found(): void
    {
        $this->get('/bao-hanh/00000000-0000-4000-8000-000000000000')
            ->assertNotFound();
    }
}
