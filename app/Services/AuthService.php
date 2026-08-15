<?php

namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function issueToken(string $username, string $password): ?array
    {
        $user = User::query()->where('username', $username)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            return null;
        }

        $rawToken = bin2hex(random_bytes(32));
        $expiresAt = now()->addMinutes(config('business.tokens.expiration_minutes'));

        ApiToken::query()->create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => $expiresAt,
        ]);

        return ['token' => $rawToken, 'expires_at' => $expiresAt->toIso8601String()];
    }

    public function revoke(ApiToken $token): void
    {
        $token->forceFill(['revoked_at' => now()])->save();
    }
}
