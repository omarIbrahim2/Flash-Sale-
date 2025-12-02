<?php

namespace App\Http\Controllers\Api\DTOs;

use App\Models\Hold;
use App\Models\Product;

class HoldCreationDto{
   
    public Product $product;


    public Hold $hold;

    public int $quantity;


    public function __construct( Product $product , int $quantity)
    {
        $this->product = $product;

        $this->quantity = $quantity;
    }


}