<?php

namespace App\Livewire;

use App\Enums\WarrantyStatus;
use App\Models\ImportBatch;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Tổng quan')]
class Dashboard extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('dashboard.view');
    }

    public function render(): View
    {
        $total = Product::query()->count();
        $active = Product::query()->withEffectiveStatus(WarrantyStatus::ACTIVE->value)->count();
        $expired = Product::query()->withEffectiveStatus(WarrantyStatus::EXPIRED->value)->count();
        $replaced = Product::query()->where('warranty_status', WarrantyStatus::REPLACED->value)->count();
        $locked = Product::query()->where('warranty_status', WarrantyStatus::LOCKED->value)->count();

        return view('livewire.dashboard', [
            'stats' => compact('total', 'active', 'expired', 'replaced', 'locked'),
            'recentProducts' => Product::query()->latest()->limit(8)->get(),
            'recentImports' => ImportBatch::query()->with('user:id,name')->latest()->limit(5)->get(),
        ]);
    }
}
