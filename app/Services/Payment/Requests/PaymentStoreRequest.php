<?php

namespace App\Services\Payment\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_name' => ['required', 'string', 'min:6'],
            'customer_phone' => ['required', 'string', 'min:8'],
            'currency' => ['required', 'string', Rule::in(['HKD', 'USD', 'AUD', 'EUR', 'JPY', 'CNY']), 'currency'],
            'amount' => ['required', 'numeric', 'regex:/^\d+\.\d{2}$/'],

            'ccname' => ['required', 'string', 'ccname'],
            'ccnumber' => ['required', 'ccnumber'],
            'ccmonth' => ['required', 'ccdate'],
            'ccyear' => ['required', ],
            'cvv' => ['required', 'cvv'],
        ];
    }

    public function messages()
    {
        return [
            'ccname' => 'Card holder name is invalid',
            'cvv' => 'CVV is not correct.',
            'ccdate' => 'Date format is invalid',
            'ccnumber' => 'Credit Card Number is invalid or not supported.',
            'currency' => 'AMEX is possible to use only for USD',
        ];
    }
}
