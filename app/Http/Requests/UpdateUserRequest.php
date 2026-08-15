<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends ApiRequest
{
    protected function allowedKeys(): array
    {
        return ['username', 'age', 'version'];
    }

    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'string', 'min:3', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($this->route('id'))],
            'age' => ['sometimes', 'integer', 'between:13,120'],
            'version' => ['required', 'integer', 'min:1'],
        ];
    }
}
