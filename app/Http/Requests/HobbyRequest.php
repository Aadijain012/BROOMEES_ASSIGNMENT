<?php

namespace App\Http\Requests;

class HobbyRequest extends ApiRequest
{
    protected function allowedKeys(): array
    {
        return ['hobby_id'];
    }

    public function rules(): array
    {
        return ['hobby_id' => ['required', 'uuid']];
    }
}
