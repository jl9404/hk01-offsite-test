<?php

namespace Tests;

use App\Services\Payment\Contracts\ResponseContract;

class FakeResponse implements ResponseContract
{

    public function isSuccessful()
    {
        return true;
    }

    public function getReferenceId()
    {
        return 'id';
    }

    public function getPaidTimestamp()
    {
        return '2017-01-01';
    }

    public function getDebugData()
    {
        return '';
    }

    public function getErrors()
    {
        return null;
    }
}
