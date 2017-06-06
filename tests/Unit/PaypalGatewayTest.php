<?php

namespace Tests\Unit;

use App\Services\Payment\Contracts\GatewayContract;
use App\Services\Payment\Gateways\Paypal;
use App\Services\Payment\Gateways\Responses\PaypalResponse;
use PayPal\Rest\ApiContext;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaypalGatewayTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mock = \Mockery::mock(Paypal::class)->makePartial();
    }

    public function testPaypalGatewayClassIsExisted()
    {
        $this->assertTrue(class_exists(Paypal::class));
    }

    public function testHasValidConfiguration()
    {
        $this->assertNotEmpty($config = config('services.paypal'));

        $this->assertArrayHasKey('environment', $config);

        $this->assertArrayHasKey('client_id', $config);

        $this->assertArrayHasKey('client_secret', $config);
    }

    public function testPaypalGatewayHasRightInterface()
    {
        $paypal = $this->mock;

        $this->assertInstanceOf(GatewayContract::class, $paypal);
    }

    public function testPaypalGatewayCanGetContext()
    {
        $paypal = $this->mock;

        $paypal->shouldReceive('getContext')->once()->andReturn(ApiContext::class);

        $paypal->getContext();
    }

    public function testPaypalGatewayCanPurchase()
    {
        $paypal = $this->mock;

        $this->assertTrue(method_exists($paypal, 'purchase'));

        $paypal->shouldReceive('purchase')->once()->andReturn(PaypalResponse::class);

        $paypal->purchase([]);
    }

}
