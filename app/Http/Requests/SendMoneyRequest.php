<?php

namespace App\Http\Requests;

class SendMoneyRequest extends FormRequestResponse
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
    public function rules(): array
    {
        return [
            'to_currency' => 'required|string',
            'amount' => 'required|numeric|min:100000',
            'receiver_email' => 'required|email',
            'note' => 'nullable|string',
        ];
    }
}
