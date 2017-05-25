<?php

namespace App\Validators;

class CreditCardValidator
{



    public function validateCvv($attribute, $value, $parameters, $validator)
    {
        //dd($validator);
        return true;
    }

}