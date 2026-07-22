<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarrantyProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $status = $this->resource->effectiveWarrantyStatus();

        return [
            'product_code' => $this->resource->product_code,
            'name' => $this->resource->name,
            'imei' => $this->resource->imei,
            'warehouse_date' => $this->resource->warehouse_date?->toDateString(),
            'warranty_months' => $this->resource->warranty_months,
            'warranty_expires_at' => $this->resource->warranty_expires_at?->toDateString(),
            'warranty_status' => $status->value,
            'warranty_status_label' => $status->label(),
            'lookup_url' => $this->resource->publicLookupUrl(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
        ];
    }
}
