<?php

namespace App\Models;

use App\Traits\HasCacheAside;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hold extends Model
{
    use HasFactory , HasCacheAside;
     protected $guarded = ['id' , 'created_at' , 'updated_at'];
     
     protected $casts = [
        'expires_at' => 'datetime',
     ];

     public function orders():HasMany{
         return $this->hasMany(Order::class);
     }

     public function product():BelongsTo{
        return $this->belongsTo(Product::class);
     }

     public function isExpired():bool{
         return $this->expires_at && $this->expires_at->isPast();
     }

     public function scopeexpired(Builder $query){
         return $query->where('expires_at' , '<' , now())
         ->whereNotNull('expires_at');
     }
}
