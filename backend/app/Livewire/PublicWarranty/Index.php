<?php

namespace App\Livewire\PublicWarranty;

use App\Http\Resources\V1\WarrantyProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Tra cứu bảo hành')]
class Index extends Component
{
    public string $imei = '';

    /** @var array<string, mixed>|null */
    public ?array $product = null;

    public string $lookupError = '';

    public bool $rateLimited = false;

    public function search(): void
    {
        $this->imei = Product::normalizeImei($this->imei);
        $this->product = null;
        $this->lookupError = '';
        $this->rateLimited = false;
        $this->resetValidation();

        $rateLimitKey = 'public-warranty-search:'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 30)) {
            $this->rateLimited = true;
            $this->lookupError = 'Bạn đã tra cứu quá nhiều lần. Vui lòng thử lại sau '.RateLimiter::availableIn($rateLimitKey).' giây.';

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        $this->validate([
            'imei' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._\-]+$/'],
        ], [
            'imei.required' => 'Vui lòng nhập IMEI.',
            'imei.regex' => 'IMEI không đúng định dạng.',
        ]);

        $product = Product::query()->where('imei', $this->imei)->first();

        if (! $product) {
            $this->lookupError = 'Không tìm thấy thông tin bảo hành.';

            return;
        }

        $this->product = (new WarrantyProductResource($product))->resolve(request());
    }

    public function render(): View
    {
        return view('livewire.public-warranty.index');
    }
}
