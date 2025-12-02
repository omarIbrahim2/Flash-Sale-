<?php

namespace App\Http\Controllers\Api\Commands;

use Closure;
use App\Models\Hold;


class HoldCreationCommand{

    public function __invoke($dto , \Closure $next)
    {
        $hold = Hold::query()->create([
                    'product_id' => $dto->product->id,
                    'qty' => $dto->quantity,
                    'expires_at' => now()->addMinutes(2),
                ]);

              $dto->hold = $hold;  
        
        return $next($dto);
    }
}