<?php

namespace Tests\Unit;

use App\Services\Payment\Contracts\GatewayContract;
use App\Services\Payment\Gateways\Braintree;
use App\Services\Payment\Gateways\Responses\BraintreeResponse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BraintreeGatewayTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mock = \Mockery::mock(Braintree::class)->makePartial();
    }

    public function testBraintreeGatewayClassIsExisted()
    {
        $this->assertTrue(class_exists(Braintree::class));
    }

    public function testHasValidConfiguration()
    {
        $this->assertNotEmpty($config = config('services.braintree'));

        $this->assertArrayHasKey('environment', $config);

        $this->assertArrayHasKey('merchant_id', $config);

        $this->assertArrayHasKey('public_key', $config);

        $this->assertArrayHasKey('private_key', $config);
    }

    public function testBraintreeGatewayHasRightInterface()
    {
        $braintree = $this->mock;

        $this->assertInstanceOf(GatewayContract::class, $braintree);
    }

    public function testBraintreeGatewayCanPurchase()
    {
        $braintree = $this->mock;

        $this->assertTrue(method_exists($braintree, 'purchase'));

        $braintree->shouldReceive('purchase')->once()->andReturn(BraintreeResponse::class);

        $braintree->purchase([]);
    }
}
