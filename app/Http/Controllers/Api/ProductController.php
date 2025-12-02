<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index($product_id){

        $product =  Product::query()->cachedFind($product_id);
         
        return new ProductResource($product);
    }
}
