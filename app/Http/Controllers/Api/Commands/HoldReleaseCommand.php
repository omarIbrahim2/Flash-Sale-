<?php

namespace App\Http\Controllers\Api\Commands;

class HoldReleaseCommand
{
    public function __invoke($dto, \Closure $next)
    {
        $dto->hold->update([
            'expires_at' => now()->subMinutes(2),
        ]);

        return $next($dto);
    }
}
