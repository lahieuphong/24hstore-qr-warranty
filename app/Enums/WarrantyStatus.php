<?php

namespace App\Enums;

enum WarrantyStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case REPLACED = 'replaced';
    case LOCKED = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Còn bảo hành',
            self::EXPIRED => 'Hết bảo hành',
            self::REPLACED => 'Đổi bảo hành',
            self::LOCKED => 'Khóa bảo hành',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::EXPIRED => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            self::REPLACED => 'bg-amber-50 text-amber-800 ring-amber-600/20',
            self::LOCKED => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }
}
