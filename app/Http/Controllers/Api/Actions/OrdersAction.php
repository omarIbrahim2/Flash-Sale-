<?php

namespace App\Http\Controllers\Api\Actions;

use App\Http\Controllers\Api\DTOs\OrderCreationDto;
use App\Models\Hold;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Str;
use App\Models\PaymentRequest;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\HoldExpiredException;
use App\Http\Controllers\Api\Commands\HoldReleaseCommand;
use App\Http\Controllers\Api\Commands\OrderCreateCommand;
use App\Http\Controllers\Api\Commands\PaymentRequestCreateCommand;


class OrdersAction
{

   public static function handle($hold_id, $product_id):Order
   {

      $lock = Cache::lock('order-' . $hold_id . '-' . $product_id, 10);

      try {
         if (!$lock->block(5)) {
            throw new \Exception('Could not acquire lock - please try again');

         }

         DB::beginTransaction();

         $hold = Hold::query()
            ->where('product_id', $product_id)
            ->findOrFail($hold_id);

         if ($hold->isExpired()) {
            throw new HoldExpiredException('the hold is expired', 422);
         }

         try {

            $creationOrderDto = app(Pipeline::class)
                  ->send(new OrderCreationDto($hold))
                  ->through([
                     OrderCreateCommand::class, //create the order
                     PaymentRequestCreateCommand::class, //intiallize the payment request
                     HoldReleaseCommand::class, //Hold expiry after order submitted
                  ])
                  ->thenReturn();

            DB::commit();
         } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
         }

         return $creationOrderDto->order;

      } finally {

         optional($lock)->release();

      }

   }
}