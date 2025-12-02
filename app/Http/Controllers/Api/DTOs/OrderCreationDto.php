<?php

namespace App\Http\Controllers\Api\DTOs;

use App\Models\Hold;
use App\Models\Order;
use App\Models\PaymentRequest;

class OrderCreationDto{


    public Order $order;

    public Hold $hold;

    public PaymentRequest $paymentRequest;


    public function __construct(Hold $hold)
    {
        $this->hold = $hold;
    }
}