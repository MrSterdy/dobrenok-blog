<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'amount',
        'currency',
        'status',
        'external_subscription_id',
        'project_id',
        'next_billing_date',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => SubscriptionStatus::class,
        'next_billing_date' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE && $this->cancelled_at === null;
    }

    public function cancel(): void
    {
        $this->status = SubscriptionStatus::CANCELLED;
        $this->cancelled_at = now();
        $this->save();
    }
}
