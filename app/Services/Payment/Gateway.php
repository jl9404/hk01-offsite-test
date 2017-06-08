<?php

namespace App\Services\Payment;

use App\Services\Payment\Contracts\FactoryContract;
use App\Services\Payment\Contracts\GatewayContract;
use App\Services\Payment\Gateways\Braintree;
use App\Services\Payment\Gateways\Paypal;
use Closure;
use Illuminate\Foundation\Application;
use InvalidArgumentException;

class Gateway implements FactoryContract
{
    protected $app;

    protected $customStores;

    protected $store;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function driver($driver)
    {
        return $this->has($driver) ? $this->store[$driver] : $this->store[$driver] = $this->get($driver);
    }

    public function has($driver)
    {
        return isset($this->store[$driver]);
    }

    public function get($driver)
    {
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
        return $this->resolve(Braintree::class, $config);
    }

    protected function createPaypalDriver(array $config = [])
    {
        return $this->resolve(Paypal::class, $config);
    }

    public function extend($driver, $class)
    {
        $this->customStores[$driver] = function ($config) use ($class) {
            return $this->resolve($class, $config);
        };
        return $this;
    }

    protected function resolve($class, $config)
    {
        return $this->app->makeWith($class, ['config' => $config]);
    }

}
