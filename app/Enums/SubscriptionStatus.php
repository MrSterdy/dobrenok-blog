<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::ACTIVE => 'Активная',
            self::CANCELLED => 'Отменена',
            self::EXPIRED => 'Истекла',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::CANCELLED => 'warning',
            self::EXPIRED => 'danger',
        };
    }
}
