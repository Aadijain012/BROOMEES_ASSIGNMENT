<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedRelationship extends Model
{
    use HasFactory;

    protected $table = 'blocked_relationships';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['user_id', 'blocked_user_id', 'created_at'];
}
