<?php

namespace App\Services;

use App\Enums\WarrantyStatus;
use App\Imports\ProductWorkbookImport;
use App\Models\ImportBatch;
use App\Models\Product;
use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ProductImportService
{
    /**
     * @return ImportBatch Kết quả import, bao gồm danh sách lỗi theo dòng.
     */
    public function import(UploadedFile|string $file, ?int $userId, ?string $originalFilename = null): ImportBatch
    {
        $batch = ImportBatch::query()->create([
            'user_id' => $userId,
            'original_filename' => $originalFilename ?: ($file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file)),
        ]);

        $errors = [];
        $total = 0;
        $success = 0;
        $seenImeis = [];

        try {
            $import = new ProductWorkbookImport;
            Excel::import($import, $file);
            $rows = $import->firstSheet->rows;

            if ($rows->isEmpty()) {
                throw new \RuntimeException('File không có dữ liệu.');
            }

            $headers = $this->buildHeaderMap(collect($rows->first()));
            $this->assertRequiredHeaders($headers);

            foreach ($rows->skip(1) as $index => $row) {
                $row = collect($row);
                $data = [];

                if ($this->isBlankRow($row)) {
                    continue;
                }

                $excelRow = $index + 1;
                $total++;

                try {
                    $data = $this->mapRow($row, $headers);
                    $data['imei'] = Product::normalizeImei((string) $data['imei']);

                    if (isset($seenImeis[$data['imei']])) {
                        throw new \InvalidArgumentException("IMEI bị trùng với dòng {$seenImeis[$data['imei']]} trong cùng file.");
                    }

                    $validator = Validator::make($data, [
                        'product_code' => ['required', 'string', 'max:100'],
                        'name' => ['required', 'string', 'max:255'],
                        'imei' => [
                            'required',
                            'string',
                            'max:64',
                            'regex:/^[A-Z0-9._-]+$/',
                            Rule::unique('products', 'imei'),
                        ],
                        'warehouse_date' => ['required', 'date'],
                        'warranty_months' => ['nullable', 'integer', 'min:1', 'max:120'],
                    ], [
                        'product_code.required' => 'Thiếu mã hàng.',
                        'name.required' => 'Thiếu tên hàng.',
                        'imei.required' => 'Thiếu IMEI.',
                        'imei.regex' => 'IMEI chỉ được chứa chữ, số, dấu chấm, gạch ngang hoặc gạch dưới.',
                        'imei.unique' => 'IMEI đã tồn tại trong hệ thống.',
                        'warehouse_date.required' => 'Thiếu ngày nhập.',
                        'warehouse_date.date' => 'Ngày nhập không hợp lệ.',
                        'warranty_months.integer' => 'Thời hạn bảo hành phải là số tháng.',
                    ]);

                    if ($validator->fails()) {
                        throw new \InvalidArgumentException(implode(' ', $validator->errors()->all()));
                    }

                    Product::query()->create([
                        ...$validator->validated(),
                        'warranty_status' => WarrantyStatus::ACTIVE,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);

                    $seenImeis[$data['imei']] = $excelRow;
                    $success++;
                } catch (Throwable $exception) {
                    $errors[] = [
                        'row' => $excelRow,
                        'imei' => (string) ($data['imei'] ?? Arr::get($row->all(), $headers['imei'] ?? -1, '')),
                        'message' => $exception->getMessage(),
                    ];
                }
            }
        } catch (Throwable $exception) {
            $errors[] = [
                'row' => 1,
                'imei' => '',
                'message' => $exception->getMessage(),
            ];
        }

        $batch->update([
            'total_rows' => $total,
            'success_rows' => $success,
            'failed_rows' => count($errors),
            'errors' => $errors ?: null,
            'completed_at' => now(),
        ]);

        return $batch->fresh();
    }

    /** @return array<string, int> */
    private function buildHeaderMap(Collection $headerRow): array
    {
        $aliases = [
            'product_code' => ['ma_hang', 'ma_san_pham', 'product_code', 'sku'],
            'name' => ['ten_hang', 'ten_san_pham', 'name', 'product_name'],
            'imei' => ['imei', 'serial', 'serial_number'],
            'warehouse_date' => ['ngay_nhap', 'ngay_nhap_kho', 'warehouse_date', 'import_date'],
            'warranty_months' => ['thoi_han_bao_hanh', 'bao_hanh_thang', 'warranty_months', 'warranty'],
        ];

        $normalized = $headerRow->map(fn ($value) => $this->normalizeHeader((string) $value));
        $map = [];

        foreach ($aliases as $field => $fieldAliases) {
            foreach ($normalized as $index => $heading) {
                if (in_array($heading, $fieldAliases, true)) {
                    $map[$field] = (int) $index;
                    break;
                }
            }
        }

        return $map;
    }

    /** @param array<string, int> $headers */
    private function assertRequiredHeaders(array $headers): void
    {
        $required = ['product_code', 'name', 'imei', 'warehouse_date'];
        $missing = array_diff($required, array_keys($headers));

        if ($missing !== []) {
            throw new \RuntimeException('Thiếu cột bắt buộc. Hãy tải file mẫu và giữ nguyên hàng tiêu đề.');
        }
    }

    /** @param array<string, int> $headers
     * @return array<string, mixed>
     */
    private function mapRow(Collection $row, array $headers): array
    {
        $warrantyMonths = isset($headers['warranty_months'])
            ? $this->nullableInteger($row->get($headers['warranty_months']))
            : null;

        return [
            'product_code' => trim((string) $row->get($headers['product_code'])),
            'name' => trim((string) $row->get($headers['name'])),
            'imei' => trim((string) $row->get($headers['imei'])),
            'warehouse_date' => $this->parseDate($row->get($headers['warehouse_date'])),
            'warranty_months' => $warrantyMonths,
        ];
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            } catch (Throwable) {
                return null;
            }
        }

        $text = trim((string) $value);

        foreach (['d/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'Y-m-d', 'm/d/Y', 'n/j/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $text);
                $errors = Carbon::getLastErrors();
                $hasErrors = is_array($errors)
                    && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0);

                if (! $hasErrors && $date->format($format) === $text) {
                    return $date->toDateString();
                }
            } catch (Throwable) {
                // Tiếp tục thử định dạng kế tiếp.
            }
        }

        return null;
    }

    private function nullableInteger(mixed $value): int|string|null
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $text = Str::lower(trim((string) $value));
        $text = str_replace(['tháng', 'thang', 'months', 'month'], '', $text);
        $text = trim($text);

        return is_numeric($text) ? (int) $text : $text;
    }

    private function normalizeHeader(string $header): string
    {
        return (string) Str::of($header)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');
    }

    private function isBlankRow(Collection $row): bool
    {
        return $row->filter(function (mixed $value): bool {
            if ($value instanceof DateTimeInterface) {
                return true;
            }

            return $value !== null && trim((string) $value) !== '';
        })->isEmpty();
    }
}
