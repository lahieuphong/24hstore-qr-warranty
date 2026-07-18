<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WarrantyLookupTest extends TestCase
{
    public function test_qr_page_reads_product_from_backend_api(): void
    {
        $token = '11111111-1111-4111-8111-111111111111';

        Http::fake([
            "http://backend.test/api/v1/warranties/{$token}" => Http::response([
                'data' => [
                    'product_code' => 'IP15-128',
                    'name' => 'iPhone 15 128GB',
                    'imei' => '012345678901234',
                    'warehouse_date' => '2026-01-01',
                    'warranty_months' => 12,
                    'warranty_expires_at' => '2027-01-01',
                    'warranty_status' => 'active',
                    'warranty_status_label' => 'Còn bảo hành',
                ],
            ]),
        ]);

        $this->get('/bao-hanh/'.$token)
            ->assertOk()
            ->assertSee('iPhone 15 128GB')
            ->assertSee('012345678901234');
    }

    public function test_frontend_handles_missing_product_without_database(): void
    {
        $token = '22222222-2222-4222-8222-222222222222';
        Http::fake(['http://backend.test/*' => Http::response([], 404)]);

        $this->get('/bao-hanh/'.$token)
            ->assertOk()
            ->assertSee('Không tìm thấy sản phẩm');
    }
}
