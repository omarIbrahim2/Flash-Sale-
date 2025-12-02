<?php

namespace App\Http\Controllers\Api\Actions;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\InvalidQuantityException;
use App\Http\Controllers\Api\Commands\HoldCreationCommand;
use App\Http\Controllers\Api\Commands\ProductDeductionCommand;
use App\Http\Controllers\Api\DTOs\HoldCreationDto;

class HoldsAction
{

    public static function reserve($product_id, $quantity):Hold
    {

        $lock = Cache::lock('hold-product-' . $product_id, 10);

        try {
            if (!$lock->block(5)) {
                throw new \Exception('Could not acquire lock - please try again');
            }

            return DB::transaction(function () use ($product_id, $quantity) {
                $product = Product::query()
                ->findOrFail($product_id);

                if (!$product->isAvaliableQuantity($quantity)) {
                    throw new InvalidQuantityException("stock has less quantity than provided!", 409);
                }

                  $creationHoldDto = app(Pipeline::class)
                  ->send(new HoldCreationDto( $product , (int)$quantity ))
                  ->through([
                     HoldCreationCommand::class, //create the hold
                     ProductDeductionCommand::class, //deduct product quantity
                  ])
                  ->thenReturn();

                return $creationHoldDto->hold;
            });

        } finally {
            optional($lock)->release();
        }
    }


}