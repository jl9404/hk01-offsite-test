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

    public function testCacheSyncWholeTable()
    {
    	$transactions = factory(Transaction::class, 3)->create();

    	$transactions->each(function ($transaction) {
    		$this->assertNotEmpty($this->getCache()->get($transaction->computeCacheKey()));
    	});

    	Cache::flush();
    	
    	$transactions->each(function ($transaction) {
            $this->assertEmpty($this->getCache()->get($transaction->computeCacheKey()));
    	});

    	(new CacheSync)->dispatch();

    	$transactions->each(function ($transaction) {
            $this->assertNotEmpty($this->getCache()->get($transaction->computeCacheKey()));
    	});

    	$this->assertEquals(3, count($this->getCache()->getKeys()));

    	Cache::flush();
    }

    protected function getCache()
    {
        return Cache::driver('redis')->tags(Transaction::class);
    }
}
