<?php

namespace App\Http\Requests;

class StoreUserRequest extends ApiRequest
{
    protected function allowedKeys(): array
    {
        return ['username', 'age', 'password'];
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:100', 'alpha_dash', 'unique:users,username'],
            'age' => ['required', 'integer', 'between:13,120'],
            'password' => ['required', 'string', 'min:12', 'max:255'],
        ];
    }
}
