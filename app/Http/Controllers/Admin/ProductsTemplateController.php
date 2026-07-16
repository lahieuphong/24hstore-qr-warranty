<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsTemplateExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductsTemplateController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        abort_unless(request()->user()?->can('products.import'), 403);

        return Excel::download(
            new ProductsTemplateExport,
            'mau-import-san-pham.xlsx',
            ExcelWriter::XLSX,
        );
    }
}
