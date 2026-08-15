<?php

namespace Tests\Feature;

use App\Exceptions\RelationshipAlreadyExistsException;
use App\Models\ApiToken;
use App\Models\Hobby;
use App\Models\User;
use App\Services\RelationshipService;
use App\Services\ReputationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BroomeesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_issue_a_hashed_expiring_token(): void
    {
        $this->postJson('/api/users', [
            'username' => 'john',
            'age' => 25,
            'password' => 'StrongPassword123!',
        ])->assertCreated()->assertJsonPath('data.username', 'john');

        $response = $this->postJson('/api/auth/token', [
            'username' => 'john',
            'password' => 'StrongPassword123!',
        ])->assertCreated()->assertJsonPath('success', true);

        $rawToken = $response->json('data.token');

        $this->assertDatabaseHas('api_tokens', [
            'user_id' => User::query()->where('username', 'john')->value('id'),
            'token_hash' => hash('sha256', $rawToken),
        ]);
        $this->assertDatabaseMissing('api_tokens', ['token_hash' => $rawToken]);
    }

    public function test_missing_invalid_expired_and_revoked_tokens_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/users')->assertUnauthorized();
        $this->getJson('/api/users', ['Authorization' => 'Bearer invalid-token'])->assertUnauthorized();

        $expired = $this->tokenFor($user, ['expires_at' => now()->subMinute()]);
        $this->getJson('/api/users', ['Authorization' => "Bearer {$expired}"])->assertUnauthorized();

        $revoked = $this->tokenFor($user, ['revoked_at' => now()]);
        $this->getJson('/api/users', ['Authorization' => "Bearer {$revoked}"])->assertUnauthorized();
    }

    public function test_user_list_is_paginated_searchable_and_protected(): void
    {
        $user = User::factory()->create(['username' => 'alpha_user']);
        User::factory(4)->create();

        $this->getJson('/api/users?search=alpha&per_page=1', $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.username', 'alpha_user');
    }

    public function test_invalid_json_content_type_and_uuid_are_rejected_with_400(): void
    {
        $this->call('POST', '/api/users', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{"username":')
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'MALFORMED_JSON');

        $this->post('/api/users', ['username' => 'john', 'age' => 25, 'password' => 'StrongPassword123!'])
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'UNSUPPORTED_CONTENT_TYPE');

        $user = User::factory()->create();
        $this->getJson('/api/users/not-a-uuid', $this->authHeaders($user))
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'INVALID_UUID');

        $this->postJson('/api/users', ['username' => 'young', 'age' => 12, 'password' => 'StrongPassword123!'])
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->postJson('/api/users', ['username' => 'strict', 'age' => 25, 'password' => 'StrongPassword123!', 'role' => 'admin'])
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_optimistic_locking_requires_the_current_version(): void
    {
        $user = User::factory()->create(['version' => 1]);
        $headers = $this->authHeaders($user);

        $this->putJson("/api/users/{$user->id}", ['age' => 30, 'version' => 1], $headers)
            ->assertOk()
            ->assertJsonPath('data.age', 30)
            ->assertJsonPath('data.version', 2);

        $this->putJson("/api/users/{$user->id}", ['age' => 31, 'version' => 1], $headers)
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'OPTIMISTIC_LOCK_CONFLICT');
    }

    public function test_tokens_cannot_modify_another_users_resource(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['version' => 1]);

        $this->putJson("/api/users/{$target->id}", ['age' => 30, 'version' => 1], $this->authHeaders($actor))
            ->assertForbidden()
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_relationships_are_mutual_and_duplicate_and_self_links_are_rejected(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        $headers = $this->authHeaders($user);

        $this->postJson("/api/users/{$user->id}/relationships", ['friend_id' => $friend->id], $headers)
            ->assertCreated();

        $this->assertDatabaseHas('relationships', ['user_id' => $user->id, 'friend_id' => $friend->id]);
        $this->assertDatabaseHas('relationships', ['user_id' => $friend->id, 'friend_id' => $user->id]);

        $this->postJson("/api/users/{$user->id}/relationships", ['friend_id' => $friend->id], $headers)
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'RELATIONSHIP_ALREADY_EXISTS');

        $this->postJson("/api/users/{$user->id}/relationships", ['friend_id' => $user->id], $headers)
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'SELF_RELATIONSHIP');

        $this->postJson("/api/users/{$user->id}/relationships", ['friend_id' => (string) Str::uuid()], $headers)
            ->assertNotFound();
    }

    public function test_relationship_deletion_removes_both_directions_and_missing_relationship_returns_404(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        $headers = $this->authHeaders($user);

        $this->postJson("/api/users/{$user->id}/relationships", ['friend_id' => $friend->id], $headers)->assertCreated();
        $this->deleteJson("/api/users/{$user->id}/relationships", ['friend_id' => $friend->id], $headers)->assertOk();

        $this->assertDatabaseMissing('relationships', ['user_id' => $user->id, 'friend_id' => $friend->id]);
        $this->assertDatabaseMissing('relationships', ['user_id' => $friend->id, 'friend_id' => $user->id]);

        $this->deleteJson("/api/users/{$user->id}/relationships", ['friend_id' => $friend->id], $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'RELATIONSHIP_NOT_FOUND');
    }

    public function test_deterministic_concurrency_protection_leaves_exactly_one_mutual_pair(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        $service = app(RelationshipService::class);

        $service->create($user, $friend->id);

        try {
            $service->create($user, $friend->id);
            $this->fail('The competing relationship write should be rejected.');
        } catch (RelationshipAlreadyExistsException) {
            $this->assertSame(2, DB::table('relationships')
                ->where(fn ($query) => $query->where('user_id', $user->id)->where('friend_id', $friend->id))
                ->orWhere(fn ($query) => $query->where('user_id', $friend->id)->where('friend_id', $user->id))
                ->count());
        }
    }

    public function test_hobby_assignment_prevents_duplicates_and_recalculates_reputation(): void
    {
        $user = User::factory()->create(['created_at' => now()->subDays(30)]);
        $friend = User::factory()->create();
        $hobby = Hobby::factory()->create();
        $headers = $this->authHeaders($user);

        $this->postJson("/api/users/{$user->id}/relationships", ['friend_id' => $friend->id], $headers)->assertCreated();
        $this->postJson("/api/users/{$friend->id}/hobbies", ['hobby_id' => $hobby->id], $this->authHeaders($friend))->assertCreated();
        $this->postJson("/api/users/{$user->id}/hobbies", ['hobby_id' => $hobby->id], $headers)->assertCreated();

        $this->postJson("/api/users/{$user->id}/hobbies", ['hobby_id' => $hobby->id], $headers)
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'HOBBY_ALREADY_ASSIGNED');

        $this->assertGreaterThan(1, (float) $user->fresh()->reputation_score);

        $this->deleteJson("/api/users/{$user->id}/hobbies", ['hobby_id' => $hobby->id], $headers)->assertOk();
        $this->assertDatabaseMissing('user_hobbies', ['user_id' => $user->id, 'hobby_id' => $hobby->id]);
    }

    public function test_reputation_formula_includes_friends_shared_hobbies_age_cap_and_block_penalty(): void
    {
        $user = User::factory()->create(['created_at' => now()->subDays(90)]);
        $friend = User::factory()->create();
        $blocked = User::factory()->create();
        $hobby = Hobby::factory()->create();
        $user->hobbies()->attach($hobby->id);
        $friend->hobbies()->attach($hobby->id);
        app(RelationshipService::class)->create($user, $friend->id);
        DB::table('blocked_relationships')->insert(['user_id' => $user->id, 'blocked_user_id' => $blocked->id, 'created_at' => now()]);

        $score = app(ReputationService::class)->calculateForUser($user->fresh());

        $this->assertSame(3.5, $score); // 1 friend + 0.5 shared hobby + 3 age cap - 1 blocked
    }

    public function test_user_deletion_is_blocked_by_relationships_or_high_reputation_and_allowed_when_eligible(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        $headers = $this->authHeaders($user);
        $this->postJson("/api/users/{$user->id}/relationships", ['friend_id' => $friend->id], $headers)->assertCreated();

        $this->deleteJson("/api/users/{$user->id}", [], $headers)
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'USER_DELETION_CONFLICT');

        $eligible = User::factory()->create(['reputation_score' => 0]);
        $this->deleteJson("/api/users/{$eligible->id}", [], $this->authHeaders($eligible))->assertNoContent();

        $highReputation = User::factory()->create(['reputation_score' => 11]);
        $this->deleteJson("/api/users/{$highReputation->id}", [], $this->authHeaders($highReputation))
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'USER_DELETION_CONFLICT');
    }

    public function test_read_and_write_limits_are_token_scoped(): void
    {
        $user = User::factory()->create();
        $headers = $this->authHeaders($user);

        for ($request = 1; $request <= 120; $request++) {
            $this->getJson('/api/users', $headers)->assertOk();
        }

        $this->getJson('/api/users', $headers)
            ->assertStatus(429)
            ->assertHeader('X-RateLimit-Limit', '120')
            ->assertHeader('Retry-After');

        $this->getJson('/api/users', $this->authHeaders($user))->assertOk();

        $target = User::factory()->create(['version' => 1]);
        $writeHeaders = $this->authHeaders($target);

        for ($version = 1; $version <= 30; $version++) {
            $this->putJson("/api/users/{$target->id}", ['age' => 30, 'version' => $version], $writeHeaders)->assertOk();
        }

        $this->putJson("/api/users/{$target->id}", ['age' => 30, 'version' => 31], $writeHeaders)
            ->assertStatus(429)
            ->assertHeader('X-RateLimit-Limit', '30');
    }

    public function test_metrics_health_and_openapi_documentation_are_available(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/health')->assertOk()->assertJsonPath('status', 'ok');
        $this->getJson('/api/metrics/reputation', $this->authHeaders($user))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['total_users', 'average_reputation', 'highest_reputation', 'lowest_reputation', 'total_relationships', 'total_shared_hobby_pairs', 'blocked_relationships']]);
        $this->get('/api/openapi.yaml')->assertOk()->assertSee('/api/auth/token');
        $this->get('/api/documentation')->assertOk()->assertSee('swagger-ui');
    }

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.$this->tokenFor($user)];
    }

    private function tokenFor(User $user, array $attributes = []): string
    {
        $rawToken = bin2hex(random_bytes(32));

        ApiToken::query()->create(array_merge([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => now()->addDay(),
        ], $attributes));

        return $rawToken;
    }
}
