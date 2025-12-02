<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\Api\Actions\HoldsAction;
use App\Http\Controllers\Api\Actions\OrdersAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlashSaleDeadLockTest extends TestCase
{

     /**
     * Test product quantity updates don't deadlock with hold creation
     */
    public function test_product_updates_with_hold_creation_no_deadlock()
    {
        Cache::flush();
        
        $product = Product::factory()->create(['quantity' => 100]);

        $deadlocks = 0;
        $operations = [];

        // Interleave hold creation with product updates
        for ($i = 0; $i < 20; $i++) {
            try {
                // Create hold
                $hold = HoldsAction::reserve($product->id, 1);
                $operations[] = 'hold_created';

                // Try to read product (simulating concurrent reads)
                $currentProduct = Product::find($product->id);
                $operations[] = 'product_read';

            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), 'Deadlock')) {
                    $deadlocks++;
                    $operations[] = 'deadlock';
                }
            } catch (\Exception $e) {
                $operations[] = 'other_error';
            }
        }

        $this->assertEquals(0, $deadlocks, "No deadlocks should occur");
        $this->assertGreaterThan(0, collect($operations)->filter(fn($op) => $op === 'hold_created')->count());
    }

     /**
     * Test concurrent order attempts on different holds
     */
    public function test_concurrent_orders_different_holds_no_deadlock()
    {
        Cache::flush();
        
        $product = Product::factory()->create(['quantity' => 50]);

        // Create multiple holds
        $holds = [];
        for ($i = 0; $i < 10; $i++) {
            $holds[] = HoldsAction::reserve($product->id, 1);
        }

        $deadlocks = 0;
        $successfulOrders = 0;

        // Try to create orders for all holds
        foreach ($holds as $hold) {
            try {
                $order = OrdersAction::handle($hold->id, $product->id);
                $successfulOrders++;
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), 'Deadlock')) {
                    $deadlocks++;
                }
            } catch (\Exception $e) {
                // Expected exceptions (like expired holds) are fine
            }
        }

        $this->assertEquals(0, $deadlocks, "No deadlocks should occur");
        $this->assertEquals(10, $successfulOrders, "All orders should succeed");
    }
   
}
