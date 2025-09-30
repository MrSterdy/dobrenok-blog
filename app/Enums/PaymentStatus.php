<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::PENDING => 'В ожидании',
            self::COMPLETED => 'Завершен',
            self::FAILED => 'Неудачный',
            self::CANCELLED => 'Отменен',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
        };
    }
}
