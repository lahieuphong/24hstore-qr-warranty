<?php

namespace App\Livewire\Admin\Imports;

use App\Models\ImportBatch;
use App\Services\ProductImportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Import Excel')]
class Index extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public mixed $file = null;

    public ?int $latestBatchId = null;

    public function mount(): void
    {
        $this->authorize('products.import');
    }

    public function import(ProductImportService $service): void
    {
        $this->authorize('products.import');

        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'Vui lòng chọn file Excel.',
            'file.mimes' => 'Chỉ chấp nhận file XLSX, XLS hoặc CSV.',
            'file.max' => 'File không được lớn hơn 10 MB.',
        ]);

        $batch = $service->import(
            $this->file->getRealPath(),
            auth()->id(),
            $this->file->getClientOriginalName(),
        );

        $this->latestBatchId = $batch->id;
        $this->reset('file');
        session()->flash(
            $batch->failed_rows > 0 ? 'warning' : 'success',
            "Import hoàn tất: {$batch->success_rows} thành công, {$batch->failed_rows} lỗi.",
        );
    }

    public function render(): View
    {
        return view('admin.livewire.imports.index', [
            'latestBatch' => $this->latestBatchId
                ? ImportBatch::query()->find($this->latestBatchId)
                : null,
            'batches' => ImportBatch::query()
                ->with('user:id,name')
                ->latest()
                ->paginate(10),
        ]);
    }
}
