<?php

namespace App\Providers;

use Illuminate\Cache\RedisTaggedCache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RedisTaggedCache::macro('getKeys', function () {
            /* @var $this RedisTaggedCache */
            $fullKey = $this->referenceKey($this->tags->getNamespace(), static::REFERENCE_KEY_FOREVER);
            $redis = $this->store->connection();
            return collect($redis->smembers($fullKey));
        });

        RedisTaggedCache::macro('getAll', function () {
            /* @var $this RedisTaggedCache */
            $redis = $this->store->connection();
            return $this->getKeys()->map(function ($key) use ($redis) {
                return unserialize($redis->get($key));
            });
        });

        FormRequest::macro('validated', function (){
            // Backport to 5.4 only() -> intersect()
            /* @var $this FormRequest */
            return $this->intersect(array_keys($this->container->call([$this, 'rules'])));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            // IDE helper
            if (class_exists('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider')) {
                $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            }
        }

    }
}
