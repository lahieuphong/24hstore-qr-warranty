<?php

namespace Tests\Unit;

use App\Enums\WarrantyStatus;
use PHPUnit\Framework\TestCase;

class WarrantyStatusTest extends TestCase
{
    public function test_all_required_status_labels_are_available(): void
    {
        $this->assertSame([
            'active' => 'Còn bảo hành',
            'expired' => 'Hết bảo hành',
            'replaced' => 'Đổi bảo hành',
            'locked' => 'Khóa bảo hành',
        ], WarrantyStatus::options());
    }
}
