<?php

namespace App\Http\Controllers;

use App\Services\Payment\CreditCard;
use App\Http\Requests\PaymentQueryRequest;
use App\Http\Requests\PaymentStoreRequest;
use App\Jobs\CacheSync;
use App\Models\Transaction;
use Carbon\Carbon;
use Facades\App\Services\Payment\Gateway;
use Illuminate\Support\Facades\Session;

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

        if (in_array($request->currency, ['USD', 'EUR', 'AUD']) || CreditCard::parse($request->ccnumber)->getType() === 'amex') {
            $gateway = Gateway::driver('paypal');
        } else if (in_array($request->currency, ['HKD', 'JPY', 'CNY'])) {
            $gateway = Gateway::driver('braintree');
        }

        if (empty($gateway)) {
            return abort(400);
        }

        $result = $gateway->purchase(array_merge($request->validated(), ['transaction_id' => $transactionId]));

        $response = [
            'success' => $result->isSuccessful(),
        ];

        if ($result->isSuccessful()) {
            $transaction = new Transaction($request->validated());
            $transaction->transaction_id = $transactionId;
            $transaction->reference_id = $result->getReferenceId();
            $transaction->paid_at = $result->getPaidTimestamp();
            $transaction->debug = serialize($result->getDebugData());
            $transaction->save();
            $response['order'] = $transaction;
            Session::regenerateToken();
            dispatch(new CacheSync($transactionId));
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
