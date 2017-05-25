<?php

namespace App\Http\Requests;

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
            'customerName' => ['required', 'string', 'min:6'],
            'customerPhone' => ['required', 'string', 'min:8'],
            'currency' => ['required', 'string', Rule::in(['HKD', 'USD', 'AUD', 'EUR', 'JPY', 'CNY'])],
            'price' => ['required', 'numeric', 'regex:/^\d+\.\d{2}$/'],

            'ccname' => ['required', 'string'],
            'ccnumber' => ['required', ],
            'ccmonth' => ['required', ],
            'ccyear' => ['required', ],
            'cvv' => ['required', 'cvv'],
        ];
    }
}
