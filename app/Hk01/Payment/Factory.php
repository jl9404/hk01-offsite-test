<?php

namespace App\Hk01\Payment;

use App\Hk01\Payment\Gateways\Braintree;

class Factory
{
    protected function createBraintreeDriver(array $config = [])
    {
        return new Braintree($config);
    }
}