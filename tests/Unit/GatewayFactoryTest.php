<?php

namespace Tests\Unit;

use App\Services\Payment\Contracts\FactoryContract;
use App\Services\Payment\Contracts\GatewayContract;
use App\Services\Payment\Gateway;
use Facades\App\Services\Payment\Gateway as GatewayFacade;
use App\Services\Payment\Gateways\Braintree;
use App\Services\Payment\Gateways\Paypal;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GatewayFactoryTest extends TestCase
{
    public function testGatewayFactoryIsExisted()
    {
        $this->assertTrue(class_exists(Gateway::class));
    }

    public function testRealtimeFacadeIsExisted()
    {
        $this->assertTrue(class_exists(GatewayFacade::class));
    }

    public function testGatewayFactoryContract()
    {
        $this->assertInstanceOf(FactoryContract::class, $this->getGateway());
    }

    public function testDefualtGatewayDrivers()
    {
        $gateway = $this->getGateway();

        $this->assertInstanceOf(Paypal::class, $paypalDriver = $gateway->driver('paypal'));

        $this->assertInstanceOf(GatewayContract::class, $paypalDriver);

        $this->assertInstanceOf(Braintree::class, $braintreeDriver = $gateway->driver('braintree'));

        $this->assertInstanceOf(GatewayContract::class, $braintreeDriver);

        $this->assertInstanceOf(Paypal::class, $this->getObjectAttribute($gateway, 'store')['paypal']);

        $this->assertInstanceOf(Braintree::class, $this->getObjectAttribute($gateway, 'store')['braintree']);
    }

    public function testGatewayFactoryIsFailedToExtendWhenConfigurationIsNotPresent()
    {
        $gateway = $this->getGateway();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver [no-config] is not defined.');

        $gateway->extend('no-config', function () {
            return 'no-config class';
        });

        $gateway->driver('no-config');
    }

    public function testGatewayFactoryIsFailedToExtendWhenDriverIsNotPresent()
    {
        $gateway = $this->getGateway();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver [stripe] is not supported.');

        $gateway->driver('stripe');
    }

    public function testGatewayFactoryIsExtendable()
    {
        $gateway = $this->getGateway();

        $gateway->extend('stripe', function () {
            return 'stripe class';
        });

        $this->assertEquals('stripe class', $gateway->driver('stripe'));
    }

    public function getGateway()
    {
        $this->app->singleton(Gateway::class);
        return $this->app->make(Gateway::class);
    }
}
