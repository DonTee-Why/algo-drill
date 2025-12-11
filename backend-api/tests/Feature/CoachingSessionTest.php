<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Stage;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoachingSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_coaching_session(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('sessions.store'), [
                'problem_id' => $problem->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('coaching_sessions', [
            'user_id' => $user->id,
            'problem_id' => $problem->id,
            'state' => Stage::Clarify->value,
        ]);
    }

    public function test_guest_cannot_create_coaching_session(): void
    {
        $problem = Problem::factory()->create();

        $response = $this->post(route('sessions.store'), [
            'problem_id' => $problem->id,
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseCount('coaching_sessions', 0);
    }

    public function test_creating_session_requires_valid_problem_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('sessions.store'), [
                'problem_id' => 'invalid-uuid',
            ]);

        $response->assertSessionHasErrors('problem_id');

        $this->assertDatabaseCount('coaching_sessions', 0);
    }

    public function test_creating_session_requires_existing_problem(): void
    {
        $user = User::factory()->create();
        $fakeUuid = '12345678-1234-1234-1234-123456789012';

        $response = $this->actingAs($user)
            ->post(route('sessions.store'), [
                'problem_id' => $fakeUuid,
            ]);

        $response->assertSessionHasErrors('problem_id');

        $this->assertDatabaseCount('coaching_sessions', 0);
    }
}
