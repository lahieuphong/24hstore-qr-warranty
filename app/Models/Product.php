<?php

namespace App\Models;

use App\Enums\WarrantyStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_code',
        'name',
        'imei',
        'warehouse_date',
        'warranty_months',
        'warranty_expires_at',
        'warranty_status',
        'internal_note',
        'qr_token',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'warehouse_date' => 'date',
            'warranty_expires_at' => 'date',
            'warranty_months' => 'integer',
            'warranty_status' => WarrantyStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            $product->qr_token ??= (string) Str::uuid();
        });

        static::saving(function (Product $product): void {
            $product->product_code = Str::upper(trim((string) $product->product_code));
            $product->imei = self::normalizeImei((string) $product->imei);
            $product->name = trim((string) $product->name);

            if ($product->warehouse_date && $product->warranty_months) {
                $product->warranty_expires_at = Carbon::parse($product->warehouse_date)
                    ->addMonthsNoOverflow((int) $product->warranty_months)
                    ->toDateString();
            } else {
                $product->warranty_expires_at = null;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function effectiveWarrantyStatus(): WarrantyStatus
    {
        $status = $this->warranty_status instanceof WarrantyStatus
            ? $this->warranty_status
            : WarrantyStatus::from((string) $this->warranty_status);

        if (
            $status === WarrantyStatus::ACTIVE
            && $this->warranty_expires_at
            && $this->warranty_expires_at->lt(today())
        ) {
            return WarrantyStatus::EXPIRED;
        }

        return $status;
    }

    public function publicLookupUrl(): string
    {
        return route('warranty.show', ['product' => $this->qr_token]);
    }

    public static function normalizeImei(string $imei): string
    {
        return Str::upper((string) preg_replace('/\s+/', '', trim($imei)));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            $needle = "%{$term}%";
            $query->whereLike('imei', $needle)
                ->orWhereLike('product_code', $needle)
                ->orWhereLike('name', $needle);
        });
    }

    public function scopeWithEffectiveStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        if ($status === WarrantyStatus::ACTIVE->value) {
            return $query->where('warranty_status', WarrantyStatus::ACTIVE->value)
                ->where(function (Builder $query): void {
                    $query->whereNull('warranty_expires_at')
                        ->orWhereDate('warranty_expires_at', '>=', today());
                });
        }

        if ($status === WarrantyStatus::EXPIRED->value) {
            return $query->where(function (Builder $query): void {
                $query->where('warranty_status', WarrantyStatus::EXPIRED->value)
                    ->orWhere(function (Builder $query): void {
                        $query->where('warranty_status', WarrantyStatus::ACTIVE->value)
                            ->whereDate('warranty_expires_at', '<', today());
                    });
            });
        }

        return $query->where('warranty_status', $status);
    }
}
