<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Hold;

use App\Models\Product;
use App\Enums\OrderStatus;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\InvalidQuantityException;
use Illuminate\Support\Facades\ParallelTesting;
use App\Http\Controllers\Api\Actions\HoldsAction;
use App\Http\Controllers\Api\Actions\OrdersAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_parallel_holds_do_not_oversell()
    {

        Cache::flush();

        $product = Product::factory()->create([
            'quantity' => 5,
            'price' => 200,
        ]);

        // Make 5 successful holds
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson("/api/holds/{$product->id}/1");
            $response->assertStatus(200);
        }

        // 6th should fail
        $response = $this->postJson("/api/holds/{$product->id}/1");


        $this->assertEquals(0, $product->fresh()->quantity);
    }

    public function test_hold_expiry_releases_stock()
    {
        $product = Product::factory()->create(['quantity' => 5]);

        $response = $this->postJson("/api/holds/{$product->id}/3")->assertStatus(200);

        $data = $response->json('data');

        $holdId = $data['hold']['id'];

        // Simulate hold expiry
        DB::table('holds')->where('id', $holdId)->update([
            'expires_at' => now()->subMinute()
        ]);

        // Run expiry job / command
        $this->artisan("app:realease-expired-holds");

        $product->refresh();

        $this->assertEquals(5, $product->quantity);
    }


    public function test_cannot_create_order_with_expired_hold()
    {
        $product = Product::factory()->create(['quantity' => 5]);

        $data = $this->postJson("/api/holds/{$product->id}/5")->json('data');
        $hold = $data['hold'];
        // Force expiry
        DB::table('holds')->where('id', $hold['id'])->update([
            'expires_at' => now()->subMinute()
        ]);

        #expected exception
        $this->expectException(\App\Exceptions\HoldExpiredException::class);
        $this->expectExceptionMessage('the hold is expired');
        $this->expectExceptionCode(422);

        OrdersAction::handle($hold['id'], $hold['product_id']);
    }


    public function test_valid_hold_creates_order_successfully()
{
    $product = Product::factory()->create(['quantity' => 5]);

    $response = $this->postJson("/api/holds/{$product->id}/3")->assertStatus(200);
    $hold = $response->json('data.hold');

    // Create order with valid hold
    $response = $this->postJson("/api/orders/{$hold['id']}/{$hold['product_id']}")
         ->assertStatus(200);

    // Verify order was created
    $this->assertDatabaseHas('orders', [
        'status' => OrderStatus::PENDING_PAYMENT->value,
    ]);

    // Verify hold expiration was extended
    $updatedHold = Hold::find($hold['id']);
    $this->assertTrue($updatedHold->expires_at->isFuture());
}


}
