<?php

namespace App\Providers;

use App\Hk01\Payment\Gateway;
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
            return $this->intersect(array_keys($this->container->call([$this, 'rules'])));
        });

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
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        $this->app->singleton(Gateway::class, function ($app) {
            return new Gateway;
        });

        \Facades\App\Hk01\Payment\Gateway::extend('stripe', function () {
            return 'xd';
        });

    }
}
