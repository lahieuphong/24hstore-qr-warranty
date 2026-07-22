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
#[Title('Thông tin bảo hành')]
class Show extends Component
{
    public string $token;

    /** @var array<string, mixed>|null */
    public ?array $product = null;

    public int $state = 200;

    public string $message = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->loadProduct();
    }

    public function reload(): void
    {
        $rateLimitKey = 'public-warranty-reload:'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 120)) {
            $this->product = null;
            $this->state = 429;
            $this->message = 'Bạn đã làm mới quá nhiều lần. Vui lòng thử lại sau '.RateLimiter::availableIn($rateLimitKey).' giây.';

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);
        $this->loadProduct();
    }

    public function render(): View
    {
        return view('livewire.public-warranty.show');
    }

    private function loadProduct(): void
    {
        $this->state = 200;
        $this->message = '';

        $product = Product::query()->where('qr_token', $this->token)->first();

        if (! $product) {
            $this->product = null;
            $this->state = 404;
            $this->message = 'Không tìm thấy thông tin bảo hành cho mã QR này.';

            return;
        }

        $this->product = (new WarrantyProductResource($product))->resolve(request());
    }
}
