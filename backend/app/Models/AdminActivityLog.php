<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'auth.login' => 'Đăng nhập',
            'auth.logout' => 'Đăng xuất',
            'product.created' => 'Thêm sản phẩm',
            'product.updated' => 'Cập nhật sản phẩm',
            'product.deleted' => 'Xóa sản phẩm',
            'import.completed' => 'Import dữ liệu',
            'user.created' => 'Thêm tài khoản',
            'user.updated' => 'Cập nhật tài khoản',
            'user.status_changed' => 'Đổi trạng thái tài khoản',
            'profile.updated' => 'Cập nhật hồ sơ',
            'profile.password_changed' => 'Đổi mật khẩu',
            default => $this->action,
        };
    }
}
