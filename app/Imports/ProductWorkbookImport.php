<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductWorkbookImport implements WithMultipleSheets
{
    public ProductRowsImport $firstSheet;

    public function __construct()
    {
        $this->firstSheet = new ProductRowsImport;
    }

    /** @return array<int, ProductRowsImport> */
    public function sheets(): array
    {
        return [0 => $this->firstSheet];
    }
}
