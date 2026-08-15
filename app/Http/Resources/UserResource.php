<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'age' => $this->age,
            'reputationScore' => $this->reputation_score,
            'hobbies' => $this->whenLoaded('hobbies', fn () => $this->hobbies->map(fn ($hobby) => [
                'id' => $hobby->id,
                'name' => $hobby->name,
            ])->values()),
            'friends' => $this->whenLoaded('friends', fn () => $this->friends->map(fn ($friend) => [
                'id' => $friend->id,
                'username' => $friend->username,
                'reputationScore' => $friend->reputation_score,
            ])->values()),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'version' => $this->version,
        ];
    }
}
