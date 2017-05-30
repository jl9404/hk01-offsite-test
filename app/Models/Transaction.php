<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_id', 'reference_id',
        'customer_name', 'customer_phone', 'currency', 'amount',
    ];

    protected $dates = [
        'paid_at',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'id', 'reference_id', 'paid_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function (Transaction $transaction) {
            Cache::driver('redis')
                ->tags(['orders', snake_case($transaction->customer_name)])
                ->forever($transaction->transaction_id, encrypt(serialize($transaction)));
        });
    }

    public static function findFromCache($customerName, $transactionId)
    {
        $transaction = Cache::tags(['orders', snake_case($customerName)])->get($transactionId);
        if (empty($transaction)) {
            throw new ModelNotFoundException;
        }
        try {
            return unserialize(decrypt($transaction));
        } catch (DecryptException $e) { // try to refresh the corrupted cache
            $transaction = (new self)->where([
                'customer_name' => $customerName,
                'transaction_id' => $transactionId,
            ])->first();
            $transaction->fireModelEvent('saved', false);
            return $transaction;
        }
    }

    public function getAmountAttribute($value)
    {
        return number_format(($value / 100), 2, '.', '');
    }

    public function setAmountAttribute($value)
    {
        return $this->attributes['amount'] =  intval(floatval($value) * 100);
    }
}