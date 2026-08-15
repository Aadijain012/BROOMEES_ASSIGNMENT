<?php

namespace App\Http\Requests;

class IssueTokenRequest extends ApiRequest
{
    protected function allowedKeys(): array
    {
        return ['username', 'password'];
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }
}
