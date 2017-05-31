<?php

namespace App\Models;

use App\Jobs\PaypalSync;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

/**
 * Class Transaction
 * @package App\Models
 */
class Transaction extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'transaction_id', 'reference_id',
        'customer_name', 'customer_phone', 'currency', 'amount',
        'paid_at',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'paid_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'id', 'reference_id', 'paid_at',
    ];

    /**
     * Register events to cache and remove cache
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function (Transaction $transaction) {
            Cache::driver('redis')
                ->tags(['orders', snake_case($transaction->customer_name)])
                ->forever($transaction->transaction_id, encrypt(serialize($transaction)));
        });

        static::deleting(function (Transaction $transaction) {
            Cache::driver('redis')
                ->tags(['orders', snake_case($transaction->customer_name)])
                ->forget($transaction->transaction_id);
        });
    }

    /**
     * Check whether the record is cached
     *
     * @return bool
     */
    public function isCached()
    {
        if ($this->exists && Cache::tags(['orders', snake_case($this->customer_name)])->has($this->transaction_id)) {
            return true;
        }
        return false;
    }

    /**
     * Check whether the record is not cached
     *
     * @return bool
     */
    public function isNotCached()
    {
        return ! $this->isCached();
    }

    /**
     * Find record from cache
     *
     * @param $customerName
     * @param $transactionId
     * @return mixed
     */
    public static function findFromCache($customerName, $transactionId)
    {
        $transaction = Cache::tags(['orders', snake_case($customerName)])->get($transactionId);
        if (empty($transaction)) {
            throw new ModelNotFoundException;
        }
        try {
            return unserialize(decrypt($transaction));
        } catch (DecryptException $e) { // try to refresh the corrupted cache
            return tap((new self)->where([
                'customer_name' => $customerName,
                'transaction_id' => $transactionId,
            ])->first())->save();
        }
    }

    /**
     * Amount attribute mutator
     *
     * @param $value
     * @return string
     */
    public function getAmountAttribute($value)
    {
        return number_format($value, 2, '.', '');
    }

}
