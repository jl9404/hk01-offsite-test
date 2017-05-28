<?php

namespace App\Hk01\Payment\Gateways\Responses;

use App\Hk01\Payment\Contracts\ResponseContract;
use Carbon\Carbon;

class PaypalResponse implements ResponseContract
{
    protected $data;
    protected $errors;

    public function __construct($data, $errors = null)
    {
        $this->data = $data;
        $this->errors = $errors;
    }

    public function isSuccessful()
    {
        return ! empty($this->data->state) && $this->data->state == 'approved';
    }

    public function getReferenceId()
    {
        return $this->data->id;
    }

    public function getPaidTimestamp()
    {
        return Carbon::parse($this->data->create_time)->timezone(config('app.timezone'));
    }

    public function getDebugData()
    {
        return $this->data;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
