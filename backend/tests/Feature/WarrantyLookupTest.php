<?php

namespace Tests\Feature;

use App\Enums\WarrantyStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarrantyLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_returns_required_fields_but_not_internal_note(): void
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

        $this->getJson(route('api.v1.warranties.show', ['product' => $product->qr_token]))
            ->assertOk()
            ->assertJsonPath('data.product_code', 'IP15-128-BLK')
            ->assertJsonPath('data.name', 'Điện thoại mẫu 128GB')
            ->assertJsonPath('data.imei', '012345678901234')
            ->assertJsonPath('data.warranty_months', 12)
            ->assertJsonPath('data.warranty_status_label', 'Còn bảo hành')
            ->assertJsonMissing(['internal_note' => 'Ghi chú tuyệt mật nội bộ']);
    }

    public function test_exact_imei_search_returns_product(): void
    {
        Product::factory()->create([
            'product_code' => 'SP-SEARCH',
            'imei' => '012345678901234',
        ]);

        $this->getJson(route('api.v1.warranties.search', ['imei' => '012 345 678 901 234']))
            ->assertOk()
            ->assertJsonPath('data.product_code', 'SP-SEARCH')
            ->assertJsonPath('data.imei', '012345678901234');
    }

    public function test_legacy_backend_qr_url_redirects_to_frontend(): void
    {
        config()->set('services.frontend.url', 'https://warranty.example.test');
        $product = Product::factory()->create();

        $this->get(route('warranty.show', ['product' => $product->qr_token]))
            ->assertRedirect('https://warranty.example.test/bao-hanh/'.$product->qr_token)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_unknown_qr_token_returns_not_found(): void
    {
        $this->getJson('/api/v1/warranties/00000000-0000-4000-8000-000000000000')
            ->assertNotFound();
    }
}
