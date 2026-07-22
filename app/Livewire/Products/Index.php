<?php

namespace App\Livewire\Products;

use App\Enums\WarrantyStatus;
use App\Models\Product;
use App\Services\Admin\AdminActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Sản phẩm & QR')]
class Index extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $status = '';

    public int $perPage = 20;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    /** @var array<int, string|int> */
    public array $selected = [];

    public bool $showForm = false;

    public bool $showDeleteModal = false;

    public bool $showQrModal = false;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public ?int $qrProductId = null;

    public string $product_code = '';

    public string $name = '';

    public string $imei = '';

    public string $warehouse_date = '';

    public mixed $warranty_months = '';

    public string $warranty_status = 'active';

    public string $internal_note = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);

        if (request()->query('action') === 'create' && auth()->user()?->can('products.create')) {
            $this->resetForm();
            $this->warehouse_date = today()->toDateString();
            $this->showForm = true;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, [10, 20, 50, 100], true)) {
            $this->perPage = 20;
        }

        $this->resetPage();
        $this->selected = [];
    }

    public function sortBy(string $field): void
    {
        abort_unless(in_array($field, ['product_code', 'name', 'imei', 'warehouse_date', 'warranty_expires_at', 'created_at'], true), 422);

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', Product::class);
        $this->resetForm();
        $this->warehouse_date = today()->toDateString();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        $this->authorize('update', $product);

        $this->resetValidation();
        $this->editingId = $product->id;
        $this->product_code = $product->product_code;
        $this->name = $product->name;
        $this->imei = $product->imei;
        $this->warehouse_date = $product->warehouse_date->toDateString();
        $this->warranty_months = $product->warranty_months ?: '';
        $this->warranty_status = $product->warranty_status->value;
        $this->internal_note = (string) $product->internal_note;
        $this->showForm = true;
    }

    public function save(AdminActivityLogger $activityLogger): void
    {
        $wasEditing = $this->editingId !== null;
        $this->imei = Product::normalizeImei($this->imei);
        $this->product_code = mb_strtoupper(trim($this->product_code));

        $product = $this->editingId ? Product::query()->findOrFail($this->editingId) : new Product;
        $this->authorize($this->editingId ? 'update' : 'create', $this->editingId ? $product : Product::class);

        $validated = $this->validate([
            'product_code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'imei' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9._\-\s]+$/',
                Rule::unique('products', 'imei')->ignore($this->editingId),
            ],
            'warehouse_date' => ['required', 'date'],
            'warranty_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'warranty_status' => ['required', Rule::enum(WarrantyStatus::class)],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ], [
            'imei.unique' => 'IMEI đã tồn tại trong hệ thống.',
            'imei.regex' => 'IMEI chỉ được chứa chữ, số, dấu chấm, gạch ngang hoặc gạch dưới.',
            'warranty_months.integer' => 'Thời hạn bảo hành phải là số tháng.',
        ]);

        $validated['warranty_months'] = $validated['warranty_months'] ?: null;
        $validated['internal_note'] = trim((string) $validated['internal_note']) ?: null;
        $validated['updated_by'] = Auth::id();

        if (! $product->exists) {
            $validated['created_by'] = Auth::id();
        }

        $product->fill($validated)->save();

        $activityLogger->record(
            $wasEditing ? 'product.updated' : 'product.created',
            ($wasEditing ? 'Cập nhật' : 'Thêm').' sản phẩm '.$product->product_code.' - IMEI '.$product->imei.'.',
            $product,
            ['product_code' => $product->product_code, 'imei' => $product->imei],
        );

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', $wasEditing ? 'Đã cập nhật sản phẩm.' : 'Đã thêm sản phẩm và tạo QR.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        $this->authorize('delete', $product);
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(AdminActivityLogger $activityLogger): void
    {
        abort_if($this->deletingId === null, 422);
        $product = Product::query()->findOrFail($this->deletingId);
        $this->authorize('delete', $product);
        $product->delete();
        $activityLogger->record(
            'product.deleted',
            'Xóa sản phẩm '.$product->product_code.' - IMEI '.$product->imei.'.',
            $product,
            ['product_code' => $product->product_code, 'imei' => $product->imei],
        );

        $this->selected = array_values(array_filter(
            $this->selected,
            fn ($id) => (int) $id !== $product->id,
        ));
        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('success', 'Đã xóa sản phẩm. IMEI vẫn được giữ duy nhất trong dữ liệu lưu trữ.');
    }

    public function showQr(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        $this->authorize('view', $product);
        $this->qrProductId = $product->id;
        $this->showQrModal = true;
    }

    public function closeQr(): void
    {
        $this->showQrModal = false;
        $this->qrProductId = null;
    }

    public function selectCurrentPage(): void
    {
        $ids = $this->productsQuery()
            ->forPage($this->getPage(), $this->perPage)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->selected = array_slice(
            array_values(array_unique([...array_map('strval', $this->selected), ...$ids])),
            0,
            500,
        );
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function render(): View
    {
        $qrProduct = $this->qrProductId ? Product::query()->find($this->qrProductId) : null;

        return view('livewire.products.index', [
            'products' => $this->productsQuery()->paginate($this->perPage),
            'statuses' => WarrantyStatus::options(),
            'qrProduct' => $qrProduct,
        ]);
    }

    private function productsQuery(): Builder
    {
        $allowedSorts = ['product_code', 'name', 'imei', 'warehouse_date', 'warranty_expires_at', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($this->perPage, [10, 20, 50, 100], true) ? $this->perPage : 20;
        $this->perPage = $perPage;

        return Product::query()
            ->with('creator:id,name')
            ->search($this->search)
            ->withEffectiveStatus($this->status)
            ->orderBy($sortField, $sortDirection)
            ->orderByDesc('id');
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'product_code',
            'name',
            'imei',
            'warehouse_date',
            'warranty_months',
            'internal_note',
        ]);
        $this->warranty_status = WarrantyStatus::ACTIVE->value;
        $this->resetValidation();
    }
}
