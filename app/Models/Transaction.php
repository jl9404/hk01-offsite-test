<?php

namespace App\Models;

use App\Models\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 * @package App\Models
 */
class Transaction extends Model
{
    use Cacheable;
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
        'id', 'reference_id', 'paid_at', 'debug'
    ];

    protected $cacheIdentifier = [
        'customer_name', 'transaction_id'
    ];

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
