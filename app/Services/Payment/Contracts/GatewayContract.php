<?php

namespace App\Services\Payment\Contracts;

interface GatewayContract
{
    public function purchase(array $data = []);
}
