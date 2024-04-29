<?php

namespace App\Http\Requests;

use App\Traits\ApiResponser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormRequestResponse extends FormRequest
{
    use ApiResponser;

    protected function failedValidation(Validator $validator)
    {
        $response = $this->failResponse(
            data: $validator->errors(),
            message: $validator->errors()->first()
        );
        throw new HttpResponseException($response);
    }
}
