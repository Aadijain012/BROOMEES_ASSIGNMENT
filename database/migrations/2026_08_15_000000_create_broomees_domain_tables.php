<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hobbies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('relationships', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('friend_id');
            $table->timestamps();

            $table->primary(['user_id', 'friend_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('friend_id')->references('id')->on('users')->restrictOnDelete();
            $table->index('friend_id');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE relationships ADD CONSTRAINT relationships_not_self_check CHECK (user_id <> friend_id)');
        }

        Schema::create('user_hobbies', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('hobby_id');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['user_id', 'hobby_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('hobby_id')->references('id')->on('hobbies')->restrictOnDelete();
            $table->index('hobby_id');
        });

        Schema::create('blocked_relationships', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('blocked_user_id');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['user_id', 'blocked_user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('blocked_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('blocked_user_id');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE blocked_relationships ADD CONSTRAINT blocked_relationships_not_self_check CHECK (user_id <> blocked_user_id)');
        }

        Schema::create('api_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('blocked_relationships');
        Schema::dropIfExists('user_hobbies');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('hobbies');
    }
};
