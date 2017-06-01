<?php

namespace App\Providers;

use App\Services\Payment\Gateway;
use Illuminate\Cache\RedisTaggedCache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
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
        FormRequest::macro('validated', function (){
            // Backport to 5.4 only() -> intersect()
            /* @var $this FormRequest */
            return $this->intersect(array_keys($this->container->call([$this, 'rules'])));
        });

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

        Validator::extend('ccname', 'App\Validators\CreditCardValidator@validateName');
        Validator::extend('currency', 'App\Validators\CreditCardValidator@validateCurrency');
        Validator::extend('ccnumber', 'App\Validators\CreditCardValidator@validateNumber');
        Validator::extend('ccdate', 'App\Validators\CreditCardValidator@validateExpDate');
        Validator::extend('cvv', 'App\Validators\CreditCardValidator@validateCvv');
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

        $this->app->singleton(Gateway::class, function ($app) {
            return new Gateway;
        });

        \Facades\App\Services\Payment\Gateway::extend('stripe', function () {
            return 'this is a customize driver';
        });

    }
}
