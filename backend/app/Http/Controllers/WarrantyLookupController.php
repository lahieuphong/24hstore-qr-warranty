<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class WarrantyLookupController extends Controller
{
    public function __invoke(Product $product): RedirectResponse
    {
        return redirect()->away($product->publicLookupUrl(), 302, [
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
