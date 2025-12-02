<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;
    protected $guarded = ['id' , 'created_at' , 'updated_at'];


    public function paymentRequest():HasOne{
         return $this->hasOne(PaymentRequest::class);
    }

    public function isPending(){
        return $this->status === OrderStatus::PENDING_PAYMENT->value;
    }
    
}
