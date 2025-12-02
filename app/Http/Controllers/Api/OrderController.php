<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Actions\OrdersAction;

class OrderController extends Controller
{
    public function __invoke($product_id , $hold_id)
    {
         $order = OrdersAction::handle($product_id , $hold_id);
         
         return response()->success('order created successfully' , 200 ,  [ "order" => $order]);
    }
}
