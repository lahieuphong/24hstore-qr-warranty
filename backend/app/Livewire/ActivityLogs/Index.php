<?php

namespace App\Livewire\ActivityLogs;

use App\Models\AdminActivityLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Hoạt động quản trị')]
class Index extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $action = '';

    public int $perPage = 25;

    public function mount(): void
    {
        $this->authorize('activity.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAction(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $logs = AdminActivityLog::query()
            ->with('user:id,name,email')
            ->when($this->search !== '', function ($query): void {
                $needle = "%{$this->search}%";
                $query->where(function ($query) use ($needle): void {
                    $query->whereLike('description', $needle)
                        ->orWhereHas('user', fn ($query) => $query
                            ->whereLike('name', $needle)
                            ->orWhereLike('email', $needle));
                });
            })
            ->when($this->action !== '', fn ($query) => $query->where('action', $this->action))
            ->latest()
            ->paginate(in_array($this->perPage, [25, 50, 100], true) ? $this->perPage : 25);

        return view('livewire.activity-logs.index', [
            'logs' => $logs,
            'actions' => AdminActivityLog::query()
                ->select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
        ]);
    }
}
