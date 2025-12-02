<?php

namespace App\Jobs;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;

class UpdateProductsExpiredHoldsStock implements ShouldQueue , ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

    try {
         Hold::query()
        ->expired()
        ->whereDoesntHave('orders')
        ->chunkById(50  ,function($holds){

             foreach ($holds as $hold) {
                    
                DB::transaction(function()use($hold){
                          $product = Product::query()->where('id' , $hold->product_id)
                            ->lockForUpdate()
                            ->first();
              
                            $hold->lockForUpdate();
                             $product->increment('quantity' , $hold->qty);
                             $hold->delete();
                });

             }
            
        });

    } catch (\Throwable $th) {
        throw $th;
    } 
       
    }


    


}
