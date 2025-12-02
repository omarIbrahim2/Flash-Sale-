<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Hold;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\Api\Actions\HoldsAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductStockRaceConditionTest extends TestCase
{
  use RefreshDatabase;

    /**
     * Test race condition: Only 1 quantity available, 2 concurrent requests
     * THIS IS THE CRITICAL TEST - Only 1 hold should succeed
     */
    public function test_only_one_hold_succeeds_when_quantity_is_one()
    {
        Cache::flush();
        
        $product = \App\Models\Product::factory()->create([
            'quantity' => 1,  // Only 1 available
            'price' => 200,
        ]);

        $successCount = 0;
        $failCount = 0;

        // Simulate 2 concurrent hold requests for the same product with quantity 1
        for ($i = 0; $i < 2; $i++) {
            try {
                HoldsAction::reserve($product->id, 1);
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        // CRITICAL ASSERTIONS
        $this->assertEquals(1, $successCount, "Exactly 1 hold should succeed");
        $this->assertEquals(1, $failCount, "Exactly 1 hold should fail");
        
        // Verify product quantity is now 0
        $product->refresh();
        $this->assertEquals(0, $product->quantity, "Product should have 0 quantity");
        
        // Verify only 1 hold was created
        $this->assertEquals(1, Hold::where('product_id', $product->id)->count(), "Only 1 hold should exist");
    }
}
