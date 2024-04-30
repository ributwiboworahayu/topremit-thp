<?php

namespace App\Http\Requests;

class PaymentRequest extends FormRequestResponse
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'api_key' => 'required|string',
            'transaction_code' => 'required|string',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_account' => 'required|string',
        ];
    }
}
