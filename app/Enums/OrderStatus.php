<?php

namespace App\Enums;


enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

   public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
    
}