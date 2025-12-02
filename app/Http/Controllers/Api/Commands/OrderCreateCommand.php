<?php

namespace App\Http\Controllers\Api\Commands;

use App\Enums\OrderStatus;

class OrderCreateCommand
{
    public function __invoke($dto, \Closure $next)
    {
        $order = $dto->hold->orders()->create([
            'status' => OrderStatus::PENDING_PAYMENT->value,
        ]);

         $dto->order = $order;

        return $next($dto);
    }
}