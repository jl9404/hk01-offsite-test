<?php

namespace App\Hk01\Payment\Contracts;

use App\Hk01\Payment\Order;

interface GatewayContract
{

    public function purchase(Order $order);

}