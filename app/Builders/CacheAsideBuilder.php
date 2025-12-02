<?php

namespace App\Builders;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CacheAsideBuilder extends Builder{

    const DEFAULT_TTL = 60 * 60 * 24;

    public function cachedFind(int $model_id, int $ttl = self::DEFAULT_TTL):Model{
       $model = $this->getModel();
       return  Cache::remember($model->getCacheKey($model_id) , $ttl , fn() => $this->findOrFail($model_id));
    }

    public function invalidateCache(Model $model){

         Cache::put($model->getCacheKey() ,  $model , self::DEFAULT_TTL);
    }


}