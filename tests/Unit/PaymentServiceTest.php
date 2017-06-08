<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Services\Payment\Contracts\ResponseContract;
use App\Services\Payment\Gateways\Braintree;
use App\Services\Payment\Gateways\Paypal;
use App\Services\Payment\Gateways\Responses\BraintreeResponse;
use App\Services\PaymentService;
use Braintree\Result\Successful;
use Mockery;
use Tests\FakeResponse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaymentServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();
        $this->mock = Mockery::mock($this->getService());
    }

    public function getService()
    {
        return app()->make(PaymentService::class);
    }

    public function testGenerateTransactionId()
    {
        $service = $this->getService();

        $this->assertRegExp('/^\d+$/', $id = $service->generateTransactionId());

        $this->assertSame($id, $this->getObjectAttribute($service, 'transactionId'));
    }

    public function testGetGateway()
    {
        $this->assertInstanceOf(Paypal::class, $this->getService()->getGateway('USD'));
        $this->assertInstanceOf(Paypal::class, $this->getService()->getGateway('AUD'));
        $this->assertInstanceOf(Paypal::class, $this->getService()->getGateway('EUR'));

        $this->assertInstanceOf(Braintree::class, $this->getService()->getGateway('HKD'));
        $this->assertInstanceOf(Braintree::class, $this->getService()->getGateway('JPY'));
        $this->assertInstanceOf(Braintree::class, $this->getService()->getGateway('CNY'));

        $this->assertNull($this->getService()->getGateway('WON'));
    }

    public function testMakePayment()
    {
        $this->mock->generateTransactionId();

        $this->mock->getGateway('HKD');

        $this->mock->shouldReceive('gateway->purchase')->andReturn(ResponseContract::class);

        $this->mock->makePayment([]);
    }

    public function testSaveRecord()
    {
        $service = $this->getService();

        $id = $service->generateTransactionId();

        $transaction = factory(Transaction::class)->make();

        $service->saveRecord($transaction->getAttributes(), new FakeResponse());

        $this->assertDatabaseHas('transactions', [
            'transaction_id' => $id
        ]);

        \Cache::flush();
    }

}
