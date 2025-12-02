<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Hold;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Str;
use App\Models\PaymentRequest;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;



class WebhookTest extends TestCase
{

     use RefreshDatabase;

    public function test_webhook_is_idempotent()
    {
        $product = Product::factory()->create(['quantity' => 5 , 'price' => 200]);
        $hold = Hold::create(['product_id' => $product->id , 'expires_at' => now()->addMinutes(2)]);
        $order = Order::factory()->create(['status' => OrderStatus::PENDING_PAYMENT->value , 'hold_id' => $hold->id]);

        $token = PaymentRequest::query()->create([
               'token' => Str::random(8),
               'order_id' => $order->id,
            ]);

        $payload = [
            'payment_token' => $token->token,
            'status' => 'success',
            'idempotency_key' => 'webhook_123'
        ];

        // First call → accepted
        $this->postJson('/api/payments/webhook', $payload)
            ->assertStatus(200);

        // Second call with same idempotency key → should not double-pay
        $this->postJson('/api/payments/webhook', $payload)
            ->assertStatus(200);

        $order->refresh();

        $this->assertEquals('paid', $order->status);
    }

    public function test_webhook_can_arrive_before_order_response()
{
   
      $product = Product::factory()->create(['quantity' => 5 , 'price' => 200]);
        $hold = Hold::create(['product_id' => $product->id , 'expires_at' => now()->addMinutes(2)]);
        $order = Order::factory()->create(['status' => OrderStatus::PENDING_PAYMENT->value , 'hold_id' => $hold->id]);

    // Create the payment token (normally created inside order creation)
    $token = PaymentRequest::create([
        'order_id' => $order->id,
         'token' => Str::random(8),
    ]);

    // Webhook comes early
    $this->postJson('/api/payments/webhook', [
        'payment_token' => $token->token,
        'status' => 'success',
        'idempotency_key' => 'early-123'
    ])->assertStatus(200);

    // Order API "finally returns"
    $order->refresh();

    $this->assertEquals('paid', $order->status);
}
  
}
