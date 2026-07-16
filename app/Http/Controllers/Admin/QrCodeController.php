<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\QrCodeService;
use Illuminate\Http\Response;

class QrCodeController extends Controller
{
    public function __invoke(Product $product, QrCodeService $qrCode): Response
    {
        $this->authorizeProduct($product);

        return response($qrCode->png($product->publicLookupUrl()))
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    private function authorizeProduct(Product $product): void
    {
        abort_unless(request()->user()?->can('view', $product), 403);
    }
}
