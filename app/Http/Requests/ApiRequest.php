<?php

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(ApiResponse::error(
            'VALIDATION_ERROR',
            'The request data is invalid.',
            400,
            $validator->errors()->toArray(),
        ));
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $unsupportedKeys = array_diff(array_keys($this->all()), $this->allowedKeys());

            if ($unsupportedKeys !== []) {
                $validator->errors()->add('fields', 'Unsupported field(s): '.implode(', ', $unsupportedKeys));
            }
        });
    }

    protected function allowedKeys(): array
    {
        return [];
    }
}
