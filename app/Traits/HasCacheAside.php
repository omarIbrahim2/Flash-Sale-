<?php


namespace App\Traits;

use App\Builders\CacheAsideBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


trait HasCacheAside{

      public function newEloquentBuilder($query): Builder
    {
        return new CacheAsideBuilder($query);
    }

     public function getCacheKey(?int $model_id = null){
        $id = $model_id ?? $this->id;
       return  class_basename(self::class) . ":" . $id;

    }

    public static function bootHasCacheAside(): void
    {
        static::saved(fn (Model $model) => $model->invalidateCache($model));
        static::deleted(fn (Model $model) => $model->invalidateCache($model));
        static::updated(fn (Model $model) => $model->invalidateCache($model));
        static::created(fn (Model $model) => $model->invalidateCache($model));
    }
}