<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class TokenManagementTest extends TestCase
{
    public function test_user_can_create_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/auth/token', [
            'name' => 'Test Token',
            'abilities' => ['read', 'write'],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'token',
            'token_id',
            'name',
            'abilities',
        ]);
        $this->assertNotNull($response->json('token'));
    }

    public function test_user_can_list_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('Token 1');
        $user->createToken('Token 2');

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/auth/tokens');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_user_can_revoke_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token');

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/auth/token/'.$token->accessToken->id);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Token revoked successfully']);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_unauthenticated_users_cannot_access_token_endpoints(): void
    {
        $response = $this->getJson('/api/auth/tokens');

        $response->assertStatus(401);
    }

    public function test_user_cannot_revoke_other_users_token(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $token = $user2->createToken('Test Token');

        $response = $this->actingAs($user1, 'sanctum')->deleteJson('/api/auth/token/'.$token->accessToken->id);

        $response->assertStatus(404);
    }
}
