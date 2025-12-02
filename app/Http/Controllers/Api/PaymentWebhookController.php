<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentEvent;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request){
          
        $idempotencyKey = $request->header('idempotency_key') ?? $request->get('idempotency_key');
        $exist = PaymentEvent::where('idempotency_key' , $idempotencyKey)->exists();

        if($exist){
            return;
        }


         PaymentEvent::query()->create([
                'idempotency_key' => $request->idempotency_key
            ]);

            $token = PaymentRequest::where('token', $request->payment_token)->lockForUpdate()->firstOrFail();
            $order = $token->order()->lockForUpdate()->firstOrFail();

            if(! $order->isPending()){
                return response()->success('already-final');
            }

             if ($request->status === 'success') {
                $order->update([
                    'status' => OrderStatus::PAID->value,
                ]);

            } else {
                $order->update(['status' => OrderStatus::CANCELLED->value]);
            }

            return response()->success('ok');
    }

    
}
