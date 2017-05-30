<?php

namespace App\Hk01\Payment;

use App\Hk01\Payment\Contracts\FactoryContract;
use App\Hk01\Payment\Gateways\Braintree;
use App\Hk01\Payment\Gateways\Paypal;
use Closure;
use InvalidArgumentException;

class Gateway implements FactoryContract
{
    protected $customStores;

    protected $store;

    public function driver($driver)
    {
        return $this->store[$driver] = $this->get($driver);
    }

    public function get($driver)
    {
        if (isset($this->store[$driver])) {
            return $this->store[$driver];
        }

        $config = config('services.' . strtolower($driver));

        if (is_null($config)) {
            throw new InvalidArgumentException("Driver [{$driver}] is not defined.");
        }

        $driverMethod = 'create' . ucfirst($driver) . 'Driver';

        if (isset($this->customStores[$driver])) {
            return $this->store[$driver] = $this->customStores[$driver]($config);
        } else {
            if (method_exists($this, $driverMethod)) {
                return $this->store[$driver] = $this->{$driverMethod}($config);
            } else {
                throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
            }
        }
    }

    protected function createBraintreeDriver(array $config = [])
    {
        return new Braintree($config);
    }

    protected function createPaypalDriver(array $config = [])
    {
        return new Paypal($config);
    }

    public function extend($driver, Closure $callback)
    {
        $this->customStores[$driver] = $callback;
        return $this;
    }

}
