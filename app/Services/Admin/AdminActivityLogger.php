<?php

namespace App\Services\Admin;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AdminActivityLogger
{
    /** @param array<string, mixed> $properties */
    public function record(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?int $userId = null,
    ): void {
        try {
            $request = app()->bound('request') ? request() : null;

            AdminActivityLog::query()->create([
                'user_id' => $userId ?? Auth::id(),
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'description' => mb_substr($description, 0, 500),
                'properties' => $properties ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
