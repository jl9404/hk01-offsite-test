<?php

namespace App\Services\Payment\Contracts;

interface FactoryContract
{
    public function driver($driver);
}
