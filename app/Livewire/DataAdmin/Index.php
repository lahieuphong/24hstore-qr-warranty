<?php

namespace App\Livewire\DataAdmin;

use App\Enums\WarrantyStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

#[Layout('layouts.admin')]
#[Title('Quản lý dữ liệu')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'table', except: 'products')]
    public string $resource = 'products';

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'sort', except: 'id')]
    public string $sortField = 'id';

    #[Url(as: 'direction', except: 'desc')]
    public string $sortDirection = 'desc';

    #[Url(as: 'limit', except: 25)]
    public int $perPage = 25;

    public int|string|null $viewingId = null;

    public function boot(): void
    {
        abort_unless(auth()->user()?->hasRole('super-admin'), 403);
    }

    public function mount(): void
    {
        $this->resource = $this->normalizeResource($this->resource);
        $this->perPage = $this->normalizePerPage($this->perPage);

        if (! $this->isSortableColumn($this->sortField)) {
            $this->resetSorting();
        }

        $this->sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr(trim($this->search), 0, 100);
        $this->viewingId = null;
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = $this->normalizePerPage($this->perPage);
        $this->viewingId = null;
        $this->resetPage();
    }

    public function updatedResource(string $resource): void
    {
        $this->selectResource($resource);
    }

    public function selectResource(string $resource): void
    {
        $this->resource = $this->normalizeResource($resource);
        $this->search = '';
        $this->viewingId = null;
        $this->resetSorting();
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        abort_unless($this->isSortableColumn($field), 422);

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->viewingId = null;
        $this->resetPage();
    }

    public function viewRecord(int|string $id): void
    {
        abort_unless(ctype_digit((string) $id), 404);

        $config = $this->currentResourceConfig();
        $exists = $this->baseQuery($config)
            ->where($config['primary_key'], (int) $id)
            ->exists();

        abort_unless($exists, 404);
        $this->viewingId = (int) $id;
    }

    public function closeRecord(): void
    {
        $this->viewingId = null;
    }

    public function render(): View
    {
        $this->resource = $this->normalizeResource($this->resource);
        $this->perPage = $this->normalizePerPage($this->perPage);

        $config = $this->currentResourceConfig();

        if (! $this->isSortableColumn($this->sortField)) {
            $this->resetSorting();
        }

        $columns = $config['columns'];
        $rows = $this->rows($config, $columns);

        return view('livewire.data-admin.index', [
            'resources' => $this->resourceCards(),
            'currentResource' => [
                'key' => $this->resource,
                'label' => $config['label'],
                'description' => $config['description'],
                'icon' => $config['icon'],
            ],
            'columns' => $columns,
            'rows' => $rows,
            'record' => $this->record($config, $columns),
        ]);
    }

    /** @param array<string, mixed> $config
     * @param  array<int, array<string, mixed>>  $columns
     */
    private function rows(array $config, array $columns): LengthAwarePaginator
    {
        $query = $this->baseQuery($config);
        $search = mb_substr(trim($this->search), 0, 100);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($config, $search): void {
                foreach ($config['searchable'] as $index => $column) {
                    $method = $index === 0 ? 'whereLike' : 'orWhereLike';
                    $query->{$method}($column, "%{$search}%");
                }
            });
        }

        $sortField = $this->isSortableColumn($this->sortField)
            ? $this->sortField
            : $config['default_sort'][0];
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $primaryKey = $config['primary_key'];

        $query->orderBy($sortField, $sortDirection);

        if ($sortField !== $primaryKey) {
            $query->orderBy($primaryKey, 'desc');
        }

        $rows = $query->paginate($this->perPage);
        $rows->setCollection($rows->getCollection()->map(
            fn (object $row): array => [
                'id' => (int) data_get($row, $primaryKey),
                'values' => collect($columns)->mapWithKeys(fn (array $column): array => [
                    $column['key'] => $this->formatValue(data_get($row, $column['key']), $column['type']),
                ])->all(),
            ],
        ));

        return $rows;
    }

    /** @param array<string, mixed> $config
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<string, mixed>|null
     */
    private function record(array $config, array $columns): ?array
    {
        if ($this->viewingId === null) {
            return null;
        }

        $row = $this->baseQuery($config)
            ->where($config['primary_key'], (int) $this->viewingId)
            ->first();

        if (! $row) {
            $this->viewingId = null;

            return null;
        }

        return [
            'id' => (int) data_get($row, $config['primary_key']),
            'values' => collect($columns)->mapWithKeys(fn (array $column): array => [
                $column['key'] => $this->formatValue(data_get($row, $column['key']), $column['type']),
            ])->all(),
        ];
    }

    /** @param array<string, mixed> $config */
    private function baseQuery(array $config): Builder
    {
        $selectedColumns = collect($config['columns'])
            ->pluck('key')
            ->unique()
            ->values()
            ->all();

        return DB::table($config['table'])->select($selectedColumns);
    }

    /** @return array<int, array<string, mixed>> */
    private function resourceCards(): array
    {
        return collect($this->resourcesConfig())
            ->map(function (array $config, string $key): array {
                $count = Schema::hasTable($config['table'])
                    ? DB::table($config['table'])->count()
                    : 0;

                return [
                    'key' => $key,
                    'label' => $config['label'],
                    'description' => $config['description'],
                    'icon' => $config['icon'],
                    'count' => $count,
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<string, array<string, mixed>> */
    private function resourcesConfig(): array
    {
        return config('data_admin.resources', []);
    }

    /** @return array<string, mixed> */
    private function currentResourceConfig(): array
    {
        return $this->resourcesConfig()[$this->normalizeResource($this->resource)];
    }

    private function normalizeResource(string $resource): string
    {
        return array_key_exists($resource, $this->resourcesConfig())
            ? $resource
            : (string) config('data_admin.default_resource', 'products');
    }

    private function normalizePerPage(int $perPage): int
    {
        $options = config('data_admin.per_page_options', [10, 25, 50, 100]);

        return in_array($perPage, $options, true) ? $perPage : 25;
    }

    private function resetSorting(): void
    {
        $config = $this->currentResourceConfig();
        $this->sortField = $config['default_sort'][0];
        $this->sortDirection = $config['default_sort'][1];
    }

    private function isSortableColumn(string $field): bool
    {
        $config = $this->currentResourceConfig();

        return collect($config['columns'])
            ->contains(fn (array $column): bool => $column['key'] === $field && ($column['sortable'] ?? true));
    }

    private function formatValue(mixed $value, string $type): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($type) {
            'boolean' => (bool) $value ? 'Đang hoạt động' : 'Đã khóa',
            'date' => $this->formatDate($value, 'd/m/Y'),
            'datetime' => $this->formatDate($value, 'd/m/Y H:i'),
            'months' => ((int) $value).' tháng',
            'warranty_status' => WarrantyStatus::tryFrom((string) $value)?->label() ?? (string) $value,
            'masked_token' => $this->maskToken((string) $value),
            'json_count' => $this->jsonCount($value),
            'long_text' => mb_strimwidth((string) $value, 0, 500, '…'),
            default => (string) $value,
        };
    }

    private function formatDate(mixed $value, string $format): string
    {
        try {
            return Carbon::parse($value)->format($format);
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private function maskToken(string $token): string
    {
        if (mb_strlen($token) <= 12) {
            return '••••••••';
        }

        return mb_substr($token, 0, 8).'…'.mb_substr($token, -4);
    }

    private function jsonCount(mixed $value): string
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded) || $decoded === []) {
            return 'Không có lỗi';
        }

        return count($decoded).' lỗi (đã ẩn nội dung nhạy cảm)';
    }
}
