<?php

namespace App\Console\Commands;

use App\Enums\WarrantyStatus;
use App\Models\Product;
use Illuminate\Console\Command;

class SyncWarrantyStatuses extends Command
{
    protected $signature = 'warranty:sync-statuses';

    protected $description = 'Chuyển sản phẩm đã quá ngày hết hạn từ còn bảo hành sang hết bảo hành.';

    public function handle(): int
    {
        $updated = Product::query()
            ->where('warranty_status', WarrantyStatus::ACTIVE->value)
            ->whereNotNull('warranty_expires_at')
            ->whereDate('warranty_expires_at', '<', today())
            ->update([
                'warranty_status' => WarrantyStatus::EXPIRED->value,
                'updated_at' => now(),
            ]);

        $this->info("Đã đồng bộ {$updated} sản phẩm.");

        return self::SUCCESS;
    }
}
