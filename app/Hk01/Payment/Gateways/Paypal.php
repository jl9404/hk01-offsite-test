<?php

namespace App\Hk01\Payment\Gateways;

use App\Hk01\Payment\Contracts\GatewayContract;
use App\Hk01\Payment\Order;

class Paypal implements GatewayContract
{
    public function purchase(Order $order)
    {
        // TODO: Implement purchase() method.
    }
}
