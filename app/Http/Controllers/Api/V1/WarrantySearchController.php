<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\WarrantyProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class WarrantySearchController extends Controller
{
    public function __invoke(Request $request): WarrantyProductResource
    {
        $validated = $request->validate([
            'imei' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._\-\s]+$/'],
        ], [
            'imei.required' => 'Vui lòng nhập IMEI.',
            'imei.regex' => 'IMEI không đúng định dạng.',
        ]);

        $imei = Product::normalizeImei($validated['imei']);
        $product = Product::query()->where('imei', $imei)->firstOrFail();

        return new WarrantyProductResource($product);
    }
}
