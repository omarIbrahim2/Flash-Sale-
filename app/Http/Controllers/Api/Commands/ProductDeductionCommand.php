<?php

namespace App\Http\Controllers\Api\Commands;


class ProductDeductionCommand{

    public function __invoke($dto , \Closure $next)
    {
        $dto->product->decrement('quantity', $dto->quantity);
        
        return $next($dto);
    }
}