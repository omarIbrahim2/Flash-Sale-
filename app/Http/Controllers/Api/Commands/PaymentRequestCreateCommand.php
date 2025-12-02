<?php

namespace App\Http\Controllers\Api\Commands;

use App\Models\PaymentRequest;
use Illuminate\Support\Str;

class PaymentRequestCreateCommand
{
    public function __invoke($dto ,  \Closure $next)
    {

        PaymentRequest::query()->create([
            'token' => Str::random(8),
            'order_id' => $dto->order->id,
        ]);

        return $next($dto);
    }
}