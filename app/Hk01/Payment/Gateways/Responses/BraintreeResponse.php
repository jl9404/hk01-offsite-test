<?php

namespace App\Hk01\Payment\Gateways\Responses;

use App\Hk01\Payment\Contracts\ResponseContract;
use Carbon\Carbon;

class BraintreeResponse implements ResponseContract
{

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function isSuccessful()
    {
        return $this->data->success === true;
    }

    public function getReferenceId()
    {
        return $this->data->transaction->id;
    }

    public function getPaidTimestamp()
    {
        return Carbon::instance($this->data->transaction->createdAt)->timezone(config('app.timezone'));
    }

    public function getDebugData()
    {
        return $this->data;
    }

    public function getErrors()
    {
        return str_replace("\n", '<br />', $this->data->message);
    }
}
