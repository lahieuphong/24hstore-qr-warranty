<?php

namespace Tests\Feature;

use App\Enums\WarrantyStatus;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_token_and_expiry_date_are_generated_automatically(): void
    {
        $product = Product::query()->create([
            'product_code' => ' sp-001 ',
            'name' => ' Sản phẩm mẫu ',
            'imei' => ' 012 345 678 901 234 ',
            'warehouse_date' => '2026-01-31',
            'warranty_months' => 1,
            'warranty_status' => WarrantyStatus::ACTIVE,
        ]);

        $this->assertNotNull($product->qr_token);
        $this->assertSame('SP-001', $product->product_code);
        $this->assertSame('012345678901234', $product->imei);
        $this->assertSame('2026-02-28', $product->warranty_expires_at?->toDateString());
    }

    public function test_soft_deleted_imei_cannot_be_reused(): void
    {
        $first = Product::factory()->create(['imei' => '012345678901234']);
        $first->delete();

        $this->expectException(QueryException::class);

        Product::factory()->create(['imei' => '012345678901234']);
    }

    public function test_active_status_is_presented_as_expired_after_expiry_date(): void
    {
        $product = Product::factory()->create([
            'warehouse_date' => today()->subMonths(13),
            'warranty_months' => 12,
            'warranty_status' => WarrantyStatus::ACTIVE,
        ]);

        $this->assertSame(WarrantyStatus::EXPIRED, $product->effectiveWarrantyStatus());
    }
}
