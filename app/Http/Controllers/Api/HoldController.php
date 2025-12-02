<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Actions\HoldsAction;

class HoldController extends Controller
{
    public function __invoke($product_id , $quantity)
    {
        $hold = HoldsAction::reserve($product_id , $quantity);

         return response()->success('Holded sucessfully' , 200  , ['hold' => $hold]);
    }
}
