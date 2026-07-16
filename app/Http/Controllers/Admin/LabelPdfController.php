<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LabelPdfController extends Controller
{
    public function single(Product $product, QrCodeService $qrCode): Response
    {
        abort_unless(request()->user()?->can('print', $product), 403);

        $label = [
            'product' => $product,
            'qr' => $qrCode->dataUri($product->publicLookupUrl(), 560, 8),
        ];

        // 40 mm x 40 mm; DomPDF dùng point (1 mm ≈ 2.83465 pt).
        $paper = [0, 0, 113.386, 113.386];

        return Pdf::loadView('admin.pdf.single-label', compact('label'))
            ->setPaper($paper)
            ->download("qr-{$product->imei}.pdf");
    }

    public function bulk(Request $request, QrCodeService $qrCode): Response
    {
        abort_unless($request->user()?->can('products.print'), 403);

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter(fn ($id) => ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(500)
            ->values();

        abort_if($ids->isEmpty(), 422, 'Chưa chọn sản phẩm để in tem.');

        $products = Product::query()->whereKey($ids)->orderBy('id')->get();
        abort_if($products->isEmpty(), 404);

        $labels = $products->map(fn (Product $product) => [
            'product' => $product,
            'qr' => $qrCode->dataUri($product->publicLookupUrl(), 420, 8),
        ]);

        return Pdf::loadView('admin.pdf.labels-a4', compact('labels'))
            ->setPaper('a4', 'portrait')
            ->download('tem-qr-'.now()->format('Ymd-His').'.pdf');
    }
}
