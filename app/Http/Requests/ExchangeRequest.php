<?php

namespace App\Http\Requests;

class ExchangeRequest extends FormRequestResponse
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
            'from_currency' => 'required|string',
            'to_currency' => 'required|string',
            'amount' => 'required|numeric|min:100000',
        ];
    }
}
