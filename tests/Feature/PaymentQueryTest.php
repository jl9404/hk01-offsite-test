<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Transaction;

class PaymentQueryTest extends TestCase
{
    use WithoutMiddleware, DatabaseMigrations;

    public function testInvalidInputs()
    {
    	$this->json('POST', '/query', [])
    		 ->assertStatus(422);

        $this->json('POST', '/query', ['customer_name' => 'Jason Leung'])
        	 ->assertStatus(422);

        $this->json('POST', '/query', ['transaction_id' => '1234567890'])
        	 ->assertStatus(422);
    }

    public function testNonExistedPaymentQuery()
    {
        $this->json('POST', '/query', ['customer_name' => 'Jason Leung', 'transaction_id' => 'non-existed'])
        	 ->assertStatus(404);
    }

    public function testExistedPaymentQuery()
    {
        $transaction = factory(Transaction::class)->create([
        	'customer_name' => 'Jason Leung',
        	'transaction_id' => '1234567890',
        ]);

        $this->assertDatabaseHas('transactions', [
        	'customer_name' => 'Jason Leung',
        	'transaction_id' => '1234567890',
        ]);

        $this->json('POST', '/query', ['customer_name' => 'Jason Leung', 'transaction_id' => '1234567890'])
        	 ->assertStatus(200);

       	Cache::flush();
    }

    public function testDeletedRecordShouldNotBeFound()
    {
        $transaction = factory(Transaction::class)->create([
        	'customer_name' => 'Jason Leung',
        	'transaction_id' => '1234567890',
        ]);

        $this->assertDatabaseHas('transactions', [
        	'customer_name' => 'Jason Leung',
        	'transaction_id' => '1234567890',
        ]);

        $this->json('POST', '/query', ['customer_name' => 'Jason Leung', 'transaction_id' => '1234567890'])
        	 ->assertStatus(200);

        $transaction->delete();

        $this->json('POST', '/query', ['customer_name' => 'Jason Leung', 'transaction_id' => '1234567890'])
             ->assertStatus(404);

       	Cache::flush();
    }
}
