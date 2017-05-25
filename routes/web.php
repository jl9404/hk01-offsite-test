<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', function () {
   return tap(csrf_token(), function (){
       //Session::regenerateToken();
   });
});

Route::get('/dev', function (){

    \Illuminate\Cache\RedisTaggedCache::macro('getAll', function () {
        $fullKey = $this->referenceKey($this->tags->getNamespace(), static::REFERENCE_KEY_FOREVER);
        $redis = $this->store->connection();
        return collect($redis->smembers($fullKey))->map(function ($key) use ($redis) {
            return unserialize($redis->get($key));
        });
    });

    dd(snake_case('Jason Leung'), Cache::getRedis()->keys('*'), Cache::getRedis()->get('laravel:tag:orders:key'), Cache::tags(['orders'])->getAll());

    //dd(\App\Hk01\Payment\Order::find('Jason Leung', '201705251625338516'));


   $o = new \App\Hk01\Payment\Gateways\Braintree();
   //dd($o->getClientToken());

   $payment = \App\Hk01\Payment\Order::create([
       'orderId' => \Carbon\Carbon::now()->format('YmdHis') . random_int(1000, 9999),
       'amount' => '10.00',
       'currency' => 'HKD',
       'name' => 'HK01',
       'phone' => '12345678',
       'ccname' => 'HK01',
       'ccnumber' => '4111111111111111',
       'ccmonth' => '12',
       'ccyear' => '33',
       'cvv' => '123',
   ]);

   $o->purchase($payment);

   //dd($payment);
});

Route::get('/', function () {
    return view('payment.form');
});


Route::post('/payment', 'PaymentController@store')->name('payment.store');