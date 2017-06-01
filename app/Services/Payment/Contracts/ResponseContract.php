<?php

namespace App\Services\Payment\Contracts;

interface ResponseContract
{
    public function isSuccessful();

    public function getReferenceId();

    public function getPaidTimestamp();

    public function getDebugData();

    public function getErrors();
}