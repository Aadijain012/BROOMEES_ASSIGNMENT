<?php

namespace App\Http\Requests;

class RelationshipRequest extends ApiRequest
{
    protected function allowedKeys(): array
    {
        return ['friend_id'];
    }

    public function rules(): array
    {
        return ['friend_id' => ['required', 'uuid']];
    }
}
