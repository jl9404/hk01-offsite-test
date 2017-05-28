<?php

namespace App\Hk01\Payment\Contracts;

interface GatewayContract
{
    public function purchase(array $data = []);
}
