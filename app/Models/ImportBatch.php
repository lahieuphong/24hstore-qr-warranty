<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_filename',
        'total_rows',
        'success_rows',
        'failed_rows',
        'errors',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
