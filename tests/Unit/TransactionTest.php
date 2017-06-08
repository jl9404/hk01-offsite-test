<?php

namespace Tests\Unit;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TransactionTest extends TestCase
{
    use DatabaseMigrations;

    public function testEmptyRecord()
    {
        $transactions = Transaction::all();

        $this->assertInstanceOf(Collection::class, $transactions);

        $this->assertEquals(0, $transactions->count());
    }

    public function testRecordCanBeFoundOnDatabase()
    {
        $transaction = factory(Transaction::class)->create();

        $this->assertDatabaseHas('transactions', [
            'customer_name' => $transaction->customer_name,
            'transaction_id' => $transaction->transaction_id,
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);

        $this->assertTrue($transaction->exists);

        Cache::flush();
    }

    public function testAmountMutator()
    {
        $transaction = factory(Transaction::class)->create(['amount' => '100']);

        $this->assertSame('100.00', $transaction->amount);
    }

    public function testCacheIsExistedOnCreation()
    {
        $transaction = factory(Transaction::class)->create();

        $cacheData = decrypt($this->getCache()->get($transaction->computeCacheKey()));

        $this->assertNotEmpty($cacheData);

        $this->assertInstanceOf(Transaction::class, $cache = Transaction::findFromCache([
            'customer_name' => $transaction->customer_name,
            'transaction_id' => $transaction->transaction_id
        ]));

        $this->assertArraySubset($cache->getAttributes(), $transaction->getAttributes());

        Cache::flush();
    }

    public function testCorruptedCacheDueToEncryptionKeyIsChanged()
    {
        $transaction = factory(Transaction::class)->create();

        $oldKey = config('app.key');

        $this->artisan('key:generate');

        $this->rebindEncrypterSingleton();

        try {
            $cache = Transaction::findFromCache([
                'customer_name' => $transaction->customer_name,
                'transaction_id' => $transaction->transaction_id
            ]);
            $this->assertInstanceOf(Transaction::class, $cache);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        Cache::flush();
    }

    public function testCacheIsDeletedAfterDeletion()
    {
        $transaction = factory(Transaction::class)->create();

        $clone = clone $transaction;

        $transaction->delete();

        $this->expectException(ModelNotFoundException::class);

        Transaction::findFromCache([
            'customer_name' => $clone->customer_name,
            'transaction_id' => $clone->transaction_id
        ]);

        Cache::flush();
    }

    protected function rebindEncrypterSingleton()
    {
        $this->app->register(EncryptionServiceProvider::class, [], true);
    }

    protected function getCache()
    {
        return Cache::driver('redis')->tags(Transaction::class);
    }

}
