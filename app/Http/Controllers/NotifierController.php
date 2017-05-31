<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Facades\App\Hk01\Payment\Gateway;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use PayPal\Api\Sale;

/**
 * Class NotifierController
 * @package App\Http\Controllers
 */
class NotifierController extends Controller
{
    /**
     * @param Request $request
     */
    public function paypal(Request $request)
    {
        $uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        $sandboxUri = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

        $client = new Client;

        $response = $client->post((config('services.paypal.environment') === 'sandbox' ? $sandboxUri : $uri), [
            'form_params' => array_merge($request->input(), ['cmd' => '_notify-validate'])
        ]);

        if ($response->getBody()->getContents() === 'VERIFIED') {
            // only insert the restful api call
            if (! empty($request->invoice)) {
                $paypal = Gateway::driver('paypal');
                $sale = Sale::get($request->txn_id, $paypal->getContext());
                $custom = json_decode($request->custom);
                Transaction::firstOrCreate(['transaction_id' => $request->invoice], [
                    'reference_id' => $sale->parent_payment,
                    'customer_name' => $custom->customer_name,
                    'customer_phone' => $custom->customer_phone,
                    'currency' => $request->mc_currency,
                    'amount' => $request->mc_gross,
                    'paid_at' => Carbon::parse($sale->create_time)->timezone(config('app.timezone'))
                ]);
            }

        }
    }
}
