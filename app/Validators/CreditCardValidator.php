<?php

namespace App\Validators;

use App\Services\Payment\CreditCard;
use Carbon\Carbon;
use Illuminate\Validation\Validator;
use Exception;

class CreditCardValidator
{

    public function validateName($attribute, $value, $parameters, Validator $validator)
    {
        return strpos($value, ' ') !== false;
    }

    public function validateCurrency($attribute, $value, $parameters, Validator $validator)
    {
        $number = array_get($validator->getData(), 'ccnumber');
        if (! empty($number) && CreditCard::parse($number)->getType() == 'amex' && $value != 'USD') {
            return false;
        }
        return true;
    }

    public function validateNumber($attribute, $value, $parameters, Validator $validator)
    {
        if (! ($creditCard = CreditCard::parse($value))->isValid()) {
            return false;
        }
        $currency = array_get($validator->getData(), 'currency');
        if (! empty($currency) && $creditCard->getType() == 'unknown' && in_array($currency, ['USD', 'EUR', 'AUD'])) {
            return false;
        }
        return true;
    }

    public function validateExpDate($attribute, $value, $parameters, Validator $validator)
    {
        if ($attribute === 'ccmonth') {
            $month = $value;
            $year = array_get($validator->getData(), 'ccyear');
            if (empty($year)) {
                return false;
            }
        } else {
            return false;
        }

        try {
            return Carbon::now()->diffInMonths(Carbon::createFromDate($year, $month, 1), false) >= 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function validateCvv($attribute, $value, $parameters, Validator $validator)
    {
        $number = array_get($validator->getData(), 'ccnumber');
        if (empty($number)) {
            return false;
        }
        return CreditCard::parse($number)->checkCvv($value);
    }

}
