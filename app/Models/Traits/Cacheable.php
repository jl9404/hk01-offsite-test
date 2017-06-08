<?php

namespace App\Models\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    public static function bootCacheable()
    {
        static::saved(function ($model) {
            if (! $model->canBeCached()) {
                return ;
            }

            Cache::driver('redis')
                ->tags(get_class($model))
                ->forever($model->computeCacheKey(), encrypt($model->getAttributes()));
        });

        static::deleting(function ($model) {
            if (! $model->canBeCached()) {
                return ;
            }

            Cache::driver('redis')
                ->tags(get_class($model))
                ->forget($model->computeCacheKey());
        });
    }

    public function canBeCached()
    {
        return count($this->cacheIdentifier) > 0;
    }

    public function computeCacheKey(array $attributes = [])
    {
        return sha1(collect($this->cacheIdentifier)->map(function ($key) use ($attributes) {
            return array_get($attributes, $key, $this->{$key});
        })->filter()->implode(','));
    }

    public static function findFromCache(array $attributes = [])
    {
        $model = new self;
        $data = Cache::driver('redis')->tags(get_class($model))->get($model->computeCacheKey($attributes));

        if (empty ($data)) {
            throw new ModelNotFoundException;
        }

        try {
            return $model->newInstance(decrypt($data), true);
        } catch (DecryptException $e) {
            return tap($model->newQuery()->where($attributes)->first())->save();
        }
    }
}
