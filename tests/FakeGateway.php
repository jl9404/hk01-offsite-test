<?php

namespace Tests;

use App\Services\Payment\Contracts\GatewayContract;

class FakeGateway implements GatewayContract
{

    public function purchase(array $data = [])
    {
        // TODO: Implement purchase() method.
    }
}
