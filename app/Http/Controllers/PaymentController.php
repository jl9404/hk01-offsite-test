<?php

namespace App\Http\Controllers;

use App\Hk01\Payment\CreditCard;
use App\Hk01\Payment\Gateways\Braintree;
use App\Hk01\Payment\Gateways\Paypal;
use App\Hk01\Payment\Order;
use App\Http\Requests\PaymentQueryRequest;
use App\Http\Requests\PaymentStoreRequest;
use App\Transaction;
use Carbon\Carbon;
use Facades\App\Hk01\Payment\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Payment;

/**
 * Class PaymentController
 * @package App\Http\Controllers
 */
class PaymentController extends Controller
{
    /**
     * @param PaymentStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PaymentStoreRequest $request)
    {
        $transactionId = Carbon::now()->format('YmdHis') . random_int(1000, 9999);

        if (in_array($request->currency, ['USD', 'EUR', 'AUD'])) {
            $gateway = Gateway::make('paypal');
        } else if (in_array($request->currency, ['HKD', 'JPY', 'CNY'])) {
            $gateway = Gateway::make('braintree');
        }

        if (empty($gateway)) {
            return abort(400);
        }

        $result = $gateway->purchase(array_merge($request->validated(), ['transaction_id' => $transactionId]));

        $transaction = null;

        $response = [
            'success' => $result->isSuccessful(),
        ];

        if ($result->isSuccessful()) {
            $transaction = new Transaction($request->validated());
            $transaction->transaction_id = $transactionId;
            $transaction->reference_id = $result->getReferenceId();
            $transaction->paid_at = $result->getPaidTimestamp();
            $transaction->save();
            $response['order'] = $transaction;
            Session::regenerateToken();
        } else {
            $response['message'] = $result->getErrors();
        }

        return response()->json($response, 201);
    }

    /**
     * @param PaymentQueryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(PaymentQueryRequest $request)
    {
        $order = Transaction::findFromCache($request->customer_name, $request->transaction_id);

        return response()->json([
           'success' => true,
            'order' => $order,
        ]);
    }
}
