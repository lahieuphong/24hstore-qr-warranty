<?php

namespace Tests\Feature;

use App\Enums\WarrantyStatus;
use App\Livewire\PublicWarranty\Index as PublicWarrantyIndex;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_check_page_is_publicly_available(): void
    {
        $this->get(route('warranty.check'))
            ->assertOk()
            ->assertSee('Nhập IMEI sản phẩm');
    }

    public function test_check_page_can_find_exact_imei_without_exposing_internal_note(): void
    {
        Product::factory()->create([
            'product_code' => 'SP-LIVEWIRE',
            'name' => 'Sản phẩm tra cứu trực tiếp',
            'imei' => '012345678901234',
            'internal_note' => 'Không được hiển thị ngoài trang quản trị',
        ]);

        Livewire::test(PublicWarrantyIndex::class)
            ->set('imei', '012 345 678 901 234')
            ->call('search')
            ->assertSet('imei', '012345678901234')
            ->assertSet('product.product_code', 'SP-LIVEWIRE')
            ->assertSee('Sản phẩm tra cứu trực tiếp')
            ->assertDontSee('Không được hiển thị ngoài trang quản trị');
    }

    public function test_qr_check_page_displays_product_without_internal_note(): void
    {
        $product = Product::factory()->create([
            'name' => 'Sản phẩm từ mã QR',
            'internal_note' => 'Bí mật nội bộ',
        ]);

        $this->get(route('warranty.show', ['token' => $product->qr_token]))
            ->assertOk()
            ->assertSee('Sản phẩm từ mã QR')
            ->assertDontSee('Bí mật nội bộ');
    }

    public function test_unknown_qr_token_has_a_friendly_result(): void
    {
        $this->get(route('warranty.show', ['token' => '00000000-0000-4000-8000-000000000000']))
            ->assertOk()
            ->assertSee('Không tìm thấy sản phẩm');
    }

    public function test_legacy_qr_url_redirects_to_canonical_check_page(): void
    {
        $product = Product::factory()->create();

        $this->get(route('warranty.legacy', ['product' => $product->qr_token]))
            ->assertRedirect(route('warranty.show', ['token' => $product->qr_token]))
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_product_lookup_url_uses_the_canonical_check_route(): void
    {
        $product = Product::factory()->create();

        $this->assertSame(
            route('warranty.show', ['token' => $product->qr_token]),
            $product->publicLookupUrl(),
        );
        $this->assertStringContainsString('/check/'.$product->qr_token, $product->publicLookupUrl());
    }

    public function test_unknown_qr_token_returns_not_found(): void
    {
        $this->getJson('/api/v1/warranties/00000000-0000-4000-8000-000000000000')
            ->assertNotFound();
    }
}
