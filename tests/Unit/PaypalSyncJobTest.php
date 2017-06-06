<?php

namespace Tests\Unit;

use App\Jobs\PaypalSync;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PaypalSyncJobTest extends TestCase
{
    use DatabaseMigrations;

    public function testClassIsExisted()
    {
        $this->assertTrue(class_exists(PaypalSync::class));
    }

    // public function testPaypalSync()
    // {
    // 	$this->assertEquals(0, count(Cache::tags(['orders'])->getKeys()));

    // 	(new PaypalSync)->dispatch();

    // 	$this->assertGreaterThan(0, count(Cache::tags(['orders'])->getKeys()));

    // 	$this->assertNotEmpty(Cache::get('paypal.lastSyncTime'));

    // 	Cache::flush();
    // }
}
