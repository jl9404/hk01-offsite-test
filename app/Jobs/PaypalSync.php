<?php

namespace App\Jobs;

use App\Models\Transaction;
use Facades\App\Hk01\Payment\Gateway;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use PayPal\Api\Payment;

class PaypalSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $query = ['count' => 1000/*, 'sort_order' => 'desc'*/];
        if (! empty($lastSyncTime = Cache::driver('file')->get('paypal.lastSyncTime'))) {
            $query['start_time'] = str_replace('+00:00', 'Z', $lastSyncTime->timezone(0)->toRfc3339String());
        }

        do {
            $paypal = Gateway::driver('paypal');
            $records = Payment::all($query, $paypal->getContext());

            if (! empty($records->next_id)) {
                $query['start_id'] = $records->next_id;
            }

            $collection = collect($records->payments);

            $ids = $collection->pluck('id');
            $syncedIds = Transaction::whereIn('reference_id', $ids)->get()->pluck('reference_id');
            $unsync = $ids->diff($syncedIds);

            if ($unsync->isNotEmpty()) {
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
    }
}
