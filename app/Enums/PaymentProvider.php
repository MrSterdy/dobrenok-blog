<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case TBANK = 'tbank';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::TBANK => 'T-Bank',
        };
    }
}
