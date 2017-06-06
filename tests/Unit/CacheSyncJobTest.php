<?php

namespace Tests\Unit;

use App\Jobs\CacheSync;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheSyncJobTest extends TestCase
{
	use DatabaseMigrations;

    public function testClassIsExisted()
    {
        $this->assertTrue(class_exists(CacheSync::class));
    }

    public function testClassConstructor()
    {
    	$job = new CacheSync;

    	$this->assertSame(null, $this->getObjectAttribute($job, 'transaction_id'));

    	$job = new CacheSync('transaction_id');

    	$this->assertSame('transaction_id', $this->getObjectAttribute($job, 'transaction_id'));
    }

    public function testCacheSyncByTransactionId()
    {
    	$transaction = factory(Transaction::class)->create();

    	$this->assertTrue($transaction->isCached());

    	Cache::flush();
    
    	$this->assertTrue($transaction->isNotCached());

    	(new CacheSync($transaction->id))->dispatch();

    	$this->assertTrue($transaction->isCached());

    	$this->assertInstanceOf(Transaction::class, Transaction::findFromCache($transaction->customer_name, $transaction->transaction_id));

    	Cache::flush();
    }

    public function testCacheSyncWholeTable()
    {
    	$transactions = factory(Transaction::class, 3)->create();

    	$transactions->each(function ($transaction) {
    		$this->assertTrue($transaction->isCached());
    	});

    	Cache::flush();
    	
    	$transactions->each(function ($transaction) {
    		$this->assertTrue($transaction->isNotCached());
    	});

    	(new CacheSync)->dispatch();

    	$transactions->each(function ($transaction) {
    		$this->assertTrue($transaction->isCached());
    	});

    	$this->assertEquals(3, count(Cache::tags(['orders'])->getKeys()));

    	Cache::flush();
    }
}
