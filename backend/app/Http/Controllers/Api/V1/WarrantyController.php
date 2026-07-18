<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\WarrantyProductResource;
use App\Models\Product;

class WarrantyController extends Controller
{
    public function __invoke(Product $product): WarrantyProductResource
    {
        return new WarrantyProductResource($product);
    }
}
