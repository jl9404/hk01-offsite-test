<?php

namespace App\Hk01\Payment\Gateways;

use App\Hk01\Payment\Contracts\GatewayContract;
use App\Hk01\Payment\Order;
use Braintree_ClientToken;
use Braintree_Configuration;
use Braintree_Transaction;

/**
 * Class Braintree
 * @package App\Hk01\Payment\Gateways
 */
class Braintree implements GatewayContract
{

    protected $clientToken;

    public function __construct()
    {
        $config = config('services.braintree');

        Braintree_Configuration::environment(array_get($config, 'environment'));
        Braintree_Configuration::merchantId(array_get($config, 'merchant_id'));
        Braintree_Configuration::publicKey(array_get($config, 'public_key'));
        Braintree_Configuration::privateKey(array_get($config, 'private_key'));

        //$this->getClientToken();
    }

    public function getClientToken()
    {
        return $this->clientToken ?: $this->clientToken = Braintree_ClientToken::generate();
    }

    public function purchase(Order $order)
    {
        $result = Braintree_Transaction::sale([
            'orderId' => $order->orderId,
            'amount' => number_format($order->price, 2),
            'merchantAccountId' => strtolower($order->currency),
            'customer' => [
                'lastName' => $order->customerName,
                'phone' => $order->customerPhone,
            ],
            'creditCard' => [
                'cardholderName' => $order->ccname,
                'number' => $order->ccnumber,
                'cvv' => $order->cvv,
                'expirationMonth' => $order->ccmonth,
                'expirationYear' => $order->ccyear,
            ],
            //'paymentMethodNonce' => nonceFromTheClient,
            'options' => [
                'submitForSettlement' => True
            ]
        ]);

        return $result;
    }

}
