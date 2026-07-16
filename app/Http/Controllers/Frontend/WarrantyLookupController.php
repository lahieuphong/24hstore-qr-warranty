<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Response;

class WarrantyLookupController extends Controller
{
    public function __invoke(Product $product): Response
    {
        return response()
            ->view('frontend.warranty.show', [
                'product' => $product,
                'status' => $product->effectiveWarrantyStatus(),
            ])
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
