<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'username',
        'age',
        'password',
        'reputation_score',
        'version',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'reputation_score' => 'float',
            'age' => 'integer',
            'version' => 'integer',
        ];
    }

    public function hobbies(): BelongsToMany
    {
        return $this->belongsToMany(Hobby::class, 'user_hobbies')->withPivot('created_at');
    }

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'relationships', 'user_id', 'friend_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }
}
