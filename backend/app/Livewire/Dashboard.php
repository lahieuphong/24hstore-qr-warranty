<?php

namespace App\Livewire;

use App\Enums\WarrantyStatus;
use App\Models\AdminActivityLog;
use App\Models\ImportBatch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin')]
#[Title('Site administration')]
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
        $imports = ImportBatch::query()->count();
        $users = User::query()->count();
        $activeUsers = User::query()->where('is_active', true)->count();

        return view('livewire.dashboard', [
            'stats' => compact('total', 'active', 'expired', 'replaced', 'locked'),
            'modules' => [
                [
                    'title' => 'Bảo hành & kho',
                    'description' => 'Quản lý sản phẩm, IMEI, QR và dữ liệu nhập kho.',
                    'items' => [
                        [
                            'name' => 'Sản phẩm',
                            'description' => 'Danh sách sản phẩm và mã QR bảo hành',
                            'count' => $total,
                            'route' => route('admin.products.index'),
                            'permission' => 'products.view',
                            'action_label' => 'Thêm mới',
                            'action_route' => route('admin.products.index', ['action' => 'create']),
                            'action_permission' => 'products.create',
                        ],
                        [
                            'name' => 'Lô import',
                            'description' => 'Nhập Excel và xem lỗi theo từng dòng',
                            'count' => $imports,
                            'route' => route('admin.imports.index'),
                            'permission' => 'products.import',
                            'action_label' => 'Import',
                            'action_route' => route('admin.imports.index'),
                            'action_permission' => 'products.import',
                        ],
                    ],
                ],
                [
                    'title' => 'Xác thực & phân quyền',
                    'description' => 'Quản lý tài khoản nội bộ và vai trò truy cập.',
                    'items' => [
                        [
                            'name' => 'Người dùng',
                            'description' => $activeUsers.' đang hoạt động / '.$users.' tài khoản',
                            'count' => $users,
                            'route' => route('admin.users.index'),
                            'permission' => 'users.manage',
                            'action_label' => 'Thêm tài khoản',
                            'action_route' => route('admin.users.index', ['action' => 'create']),
                            'action_permission' => 'users.manage',
                        ],
                    ],
                ],
                [
                    'title' => 'Vận hành hệ thống',
                    'description' => 'Kiểm tra hoạt động và cấu hình triển khai.',
                    'items' => [
                        [
                            'name' => 'Hoạt động quản trị',
                            'description' => 'Nhật ký đăng nhập và thay đổi dữ liệu',
                            'count' => AdminActivityLog::query()->count(),
                            'route' => route('admin.activity.index'),
                            'permission' => 'activity.view',
                            'action_label' => null,
                            'action_route' => null,
                            'action_permission' => null,
                        ],
                    ],
                ],
            ],
            'recentActivities' => AdminActivityLog::query()
                ->with('user:id,name,email')
                ->latest()
                ->limit(8)
                ->get(),
            'system' => $this->systemStatus(),
        ]);
    }

    /** @return array<string, mixed> */
    private function systemStatus(): array
    {
        $databaseOk = true;

        try {
            DB::select('select 1');
        } catch (Throwable) {
            $databaseOk = false;
        }

        return [
            'database_ok' => $databaseOk,
            'database_driver' => (string) config('database.default'),
            'queue_driver' => (string) config('queue.default'),
            'cache_store' => (string) config('cache.default'),
            'storage_writable' => is_writable(storage_path()) && is_writable(base_path('bootstrap/cache')),
            'environment' => app()->environment(),
            'frontend_url' => (string) config('services.frontend.url'),
        ];
    }
}
