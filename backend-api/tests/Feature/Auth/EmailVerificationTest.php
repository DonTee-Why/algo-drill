<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertInertia(fn ($page) => $page->component('Auth/VerifyEmail'));
    }

    public function test_email_can_be_verified(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect('/dashboard');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email@example.com')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_verification_notification_can_be_sent(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->get('/email/verify');

        $response->assertInertia(fn ($page) => $page->component('Auth/VerifyEmail'));

        $response = $this->actingAs($user)
            ->post('/email/verification-notification');

        $response->assertStatus(302);
    }

    public function test_email_verification_notification_not_sent_if_already_verified(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/email/verify');

        $response = $this->actingAs($user)
            ->post('/email/verification-notification');

        $response->assertRedirect('/dashboard');
    }
}
