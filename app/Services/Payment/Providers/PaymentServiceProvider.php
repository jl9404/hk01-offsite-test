<?php

namespace App\Services\Payment\Providers;

use App\Services\Payment\Gateway;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
	public function boot()
	{
        Validator::extend('ccname', 'App\Services\Payment\Validators\CreditCardValidator@validateName');
        Validator::extend('currency', 'App\Services\Payment\Validators\CreditCardValidator@validateCurrency');
        Validator::extend('ccnumber', 'App\Services\Payment\Validators\CreditCardValidator@validateNumber');
        Validator::extend('ccdate', 'App\Services\Payment\Validators\CreditCardValidator@validateExpDate');
        Validator::extend('cvv', 'App\Services\Payment\Validators\CreditCardValidator@validateCvv');
	}

	public function register()
	{
		$this->app->singleton(Gateway::class);
	}
}
