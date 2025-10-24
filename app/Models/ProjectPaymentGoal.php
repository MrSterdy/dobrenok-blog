<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPaymentGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'target_amount',
        'current_amount',
        'currency',
        'description',
        'deadline',
        'is_active',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline' => 'date',
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Получить процент выполнения цели
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        return min(100, ($this->current_amount / $this->target_amount) * 100);
    }

    /**
     * Получить оставшуюся сумму до цели
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Проверить, достигнута ли цель
     */
    public function isGoalReached(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Добавить средства к текущей сумме
     */
    public function addPayment(float $amount): void
    {
        $this->increment('current_amount', $amount);
    }
}
