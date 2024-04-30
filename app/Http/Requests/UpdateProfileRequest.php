<?php

namespace App\Http\Requests;

class UpdateProfileRequest extends FormRequestResponse
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
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'address' => 'required|string',
            'country' => 'required|string',
        ];
    }
}
