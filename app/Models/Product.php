<?php

namespace App\Models;

use App\Traits\HasCacheAside;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory , HasCacheAside;

    protected $guarded = ['id' , 'created_at' , 'updated_at'];

    public function holds():HasMany{
        return $this->hasMany(Hold::class);
    }

    public function isAvaliableQuantity($quantity){
         return $this->quantity >= $quantity;
    }

    public function decrementQuantity($amount){
         $this->quantity -= $amount;
         $this->save();
    }


   
}
