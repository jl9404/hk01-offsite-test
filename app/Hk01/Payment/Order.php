<?php

namespace App\Hk01\Payment;

use Illuminate\Support\Facades\Cache;

/**
 * Class Order
 * @package App\Hk01\Payment
 */
class Order
{
    /**
     * @var array
     */
    protected $parameterBag = [];

    /**
     * Order constructor.
     * @param $parameters
     */
    public function __construct($parameters)
    {
        foreach ($parameters as $key => $value)
        {
            array_set($this->parameterBag, $key, $value);
        }
    }

    /**
     * @param array ...$args
     * @return Order
     */
    public static function create(...$args)
    {
        return new self(...$args);
    }

    /**
     * @param $name
     * @param $orderId
     * @return Order|null
     */
    public static function find($name, $orderId)
    {
        $order = Cache::tags(['orders', snake_case($name)])->get($orderId);
        if (empty($order)) {
            return null;
        }
        return new self($order);
    }

    /**
     * @param bool $withChd
     * @return bool
     */
    public function save($withChd = false)
    {
        if (! $withChd) {
            array_forget($this->parameterBag, ['ccname', 'ccnumber', 'cvv', 'ccmonth', 'ccyear']);
        }
        Cache::tags(['orders', snake_case($this->customerName)])->forever($this->orderId, $this->parameterBag);
        return true;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->parameterBag;
    }

    /**
     * @param $name
     * @param $value
     * @return array
     */
    public function __set($name, $value)
    {
        return array_set($this->parameterBag, $name, $value);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return array_get($this->parameterBag, $name, null);
    }
}
