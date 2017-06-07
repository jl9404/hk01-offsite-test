<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Payment\Contracts\ResponseContract;
use App\Services\Payment\CreditCard;
use App\Services\Payment\Gateway as GatewayFactory;
use App\Services\Payment\Requests\PaymentStoreRequest;
use Carbon\Carbon;

class PaymentService
{
    protected $gatewayFactory;
    protected $gateway;
    protected $transactionId;

    public function __construct(GatewayFactory $gateway)
    {
        $this->gatewayFactory = $gateway;
    }

    public function generateTransactionId()
    {
        return $this->transactionId = Carbon::now()->format('YmdHis') . random_int(1000, 9999);
    }

    public function getGateway($currency)
    {
        if (in_array($currency, ['USD', 'EUR', 'AUD'])) { // amex usd validation is done in formrequest
            return $this->gateway = $this->gatewayFactory->driver('paypal');
        } else if (in_array($currency, ['HKD', 'JPY', 'CNY'])) {
            return $this->gateway = $this->gatewayFactory->driver('braintree');
        }
        return null;
    }

    public function makePayment(array $attributes = [])
    {
        return $this->gateway->purchase(array_merge($attributes, ['transaction_id' => $this->transactionId]));
    }

    public function saveRecord(array $attributes = [], ResponseContract $response)
    {
        $transaction = new Transaction($attributes);
        $transaction->transaction_id = $this->transactionId;
        $transaction->reference_id = $response->getReferenceId();
        $transaction->paid_at = $response->getPaidTimestamp();
        $transaction->debug = serialize($response->getDebugData());
        $transaction->save();
        return $transaction;
    }

}
