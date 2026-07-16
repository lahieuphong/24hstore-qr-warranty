<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductRowsImport implements ToCollection
{
    /** @var Collection<int, Collection<int, mixed>> */
    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $collection): void
    {
        $this->rows = $collection;
    }
}
