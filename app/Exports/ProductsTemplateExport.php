<?php

namespace App\Exports;

use DateTimeImmutable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductsTemplateExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings
{
    public function headings(): array
    {
        return [
            'Mã hàng',
            'Tên hàng',
            'IMEI',
            'Ngày nhập',
            'Thời hạn bảo hành',
        ];
    }

    public function array(): array
    {
        return [
            ['IP15-128-BLK', 'Điện thoại mẫu 128GB', '012345678901234', new DateTimeImmutable('2026-07-15'), 12],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => 'dd/mm/yyyy',
            'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
