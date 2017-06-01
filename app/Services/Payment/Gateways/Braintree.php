<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\GatewayContract;
use App\Services\Payment\Gateways\Responses\BraintreeResponse;
use Braintree_ClientToken;
use Braintree_Configuration;
use Braintree_Transaction;
use Illuminate\Support\Arr;

/**
 * Class Braintree
 * @package App\Services\Payment\Gateways
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

    public function purchase(array $data = [])
    {
        $result = Braintree_Transaction::sale([
            'orderId' => Arr::get($data, 'transaction_id'),
            'amount' => number_format(Arr::get($data, 'amount'), 2, '.', ''),
            'merchantAccountId' => strtolower(Arr::get($data, 'currency')),
            'customer' => [
                'lastName' => Arr::get($data, 'customer_name'),
                'phone' => Arr::get($data, 'customer_phone'),
            ],
            'creditCard' => [
                'cardholderName' => Arr::get($data, 'ccname'),
                'number' => Arr::get($data, 'ccnumber'),
                'cvv' => Arr::get($data, 'cvv'),
                'expirationMonth' => Arr::get($data, 'ccmonth'),
                'expirationYear' => Arr::get($data, 'ccyear'),
            ],
            //'paymentMethodNonce' => nonceFromTheClient,
            'options' => [
                'submitForSettlement' => True
            ]
        ]);

        return new BraintreeResponse($result);
    }

}
