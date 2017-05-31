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

use App\Models\Transaction;
use Facades\App\Hk01\Payment\Gateway;
use PayPal\Api\Payment;

Route::get('/dev', function (){

    Transaction::all()->map(function (Transaction $transaction) {
        //dd(get_class($transaction));
        $transaction->save();
    });

    dd('asd');

    $query = ['count' => 1000, 'sort_order' => 'desc'];
    if (! empty($lastSyncTime = Cache::driver('file')->get('paypal.lastSyncTime'))) {
        $query['start_time'] = str_replace('+00:00', 'Z', $lastSyncTime->timezone(0)->toRfc3339String());
    }

    do {
        $paypal = Gateway::driver('paypal');
        $records = Payment::all($query, $paypal->getContext());

        if (! empty($records->next_id)) {
            $query['start_id'] = $records->next_id;
        }

        ///dd($test);
        $collection = collect($records->payments);

        $ids = $collection->pluck('id');

        $syncedIds = Transaction::whereIn('reference_id', $ids)->get()->pluck('reference_id');



        $unsync = $ids->diff($syncedIds);
        //dd($unsync);
        if ($unsync->isNotEmpty()) {
          //  dd($unsync, 'wtf');
            $unsync->each(function ($id) use ($collection) {
                $payment = $collection->where('id', $id)->first();
                if ($payment->intent == 'sale') {
                    $transaction = new Transaction();
                    $transaction->transaction_id = $payment->transactions[0]->invoice_number;
                    $transaction->reference_id = $payment->id;
                    if (! empty($payment->transactions[0]->custom)) {
                        $custom = json_decode($payment->transactions[0]->custom);
                        $transaction->customer_name = $custom->customer_name;
                        $transaction->customer_phone = $custom->customer_phone;
                    } else {
                        $transaction->customer_name = 'Jason Leung';
                        $transaction->customer_phone = '63092067';
                    }
                    $transaction->currency = $payment->transactions[0]->amount->currency;
                    $transaction->amount = $payment->transactions[0]->amount->total;
                    $transaction->paid_at = \Carbon\Carbon::parse($payment->create_time)->timezone(config('app.timezone'));
                    $transaction->save();
                }
            });
        }



    } while(! empty($records->next_id));

    Cache::driver('file')->forever('paypal.lastSyncTime', \Carbon\Carbon::now());

    dd(Cache::driver('file')->get('paypal.lastSyncTime'));

    $card = '4615304456677579';

    dd(\App\Hk01\Payment\CreditCard::parse($card)->checkLuhn());

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
})->name('payment.form');
Route::get('/query', function () {
    return view('payment.query');
})->name('payment.query');


Route::group(['middleware' => 'throttle:5,10'], function() {
    Route::post('/payment', 'PaymentController@store')->name('payment.store');
    Route::post('/query', 'PaymentController@query');
});

Route::post('/webhook/paypal', 'WebhookController@paypal');
Route::post('/notify/paypal', 'NotifierController@paypal')->name('notify.paypal');