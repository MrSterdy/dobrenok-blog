<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'integration_id',
        'status',
        'external_id',
        'external_url',
        'error_message',
        'published_at',
        'retry_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * Проверить, была ли публикация успешной
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Проверить, была ли ошибка при публикации
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Проверить, ожидает ли публикация
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
