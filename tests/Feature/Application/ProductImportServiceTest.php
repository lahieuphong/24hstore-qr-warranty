<?php

namespace Tests\Feature\Application;

use App\Models\Product;
use App\Services\ProductImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_creates_valid_rows_and_reports_duplicate_imei(): void
    {
        $directory = storage_path('framework/testing');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/products-import.csv';

        $handle = fopen($path, 'wb');
        fputcsv($handle, ['Mã hàng', 'Tên hàng', 'IMEI', 'Ngày nhập', 'Thời hạn bảo hành']);
        fputcsv($handle, ['SP-001', 'Sản phẩm 1', '012345678901234', '15/07/2026', '12 tháng']);
        fputcsv($handle, ['SP-002', 'Sản phẩm 2', '012345678901234', '16/07/2026', '12']);
        fclose($handle);

        $batch = app(ProductImportService::class)->import($path, null, 'products-import.csv');

        $this->assertSame(2, $batch->total_rows);
        $this->assertSame(1, $batch->success_rows);
        $this->assertSame(1, $batch->failed_rows);
        $this->assertStringContainsString('trùng', mb_strtolower($batch->errors[0]['message']));
        $this->assertDatabaseHas('products', [
            'product_code' => 'SP-001',
            'imei' => '012345678901234',
            'warranty_months' => 12,
        ]);

        $product = Product::query()->where('imei', '012345678901234')->sole();

        $this->assertSame('2027-07-15', $product->warranty_expires_at?->toDateString());
    }

    public function test_invalid_warranty_text_is_reported_instead_of_silently_ignored(): void
    {
        $directory = storage_path('framework/testing');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/products-invalid-warranty.csv';

        $handle = fopen($path, 'wb');
        fputcsv($handle, ['Mã hàng', 'Tên hàng', 'IMEI', 'Ngày nhập', 'Thời hạn bảo hành']);
        fputcsv($handle, ['SP-003', 'Sản phẩm 3', '987654321098765', '15/07/2026', 'mười hai']);
        fclose($handle);

        $batch = app(ProductImportService::class)->import($path, null, 'products-invalid-warranty.csv');

        $this->assertSame(0, $batch->success_rows);
        $this->assertSame(1, $batch->failed_rows);
        $this->assertDatabaseMissing('products', ['imei' => '987654321098765']);
    }
}
