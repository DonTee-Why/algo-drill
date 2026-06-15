<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CoachClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachClientTest extends TestCase
{
    public function test_critique_posts_payload_and_returns_success_response(): void
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response([
                'coach_msg' => 'Looks good.',
                'scores' => [
                    'inputs_outputs' => ['score' => 3, 'max_score' => 3, 'reason' => 'Clear'],
                ],
                'flags' => ['too_vague' => false],
                'questions' => [],
            ], 200),
        ]);

        $client = new CoachClient;
        $payload = ['session_id' => 'test-session', 'stage' => 'CLARIFY'];

        $response = $client->critique($payload);

        $this->assertTrue($response['success']);
        $this->assertSame('Looks good.', $response['data']['coach_msg']);

        Http::assertSent(function ($request) use ($payload) {
            return $request->url() === 'http://coach.test/coach/critique'
                && $request->method() === 'POST'
                && $request->data() === $payload;
        });
    }

    public function test_critique_returns_failure_when_service_errors(): void
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response([
                'message' => 'Service unavailable',
            ], 503),
        ]);

        $client = new CoachClient;
        $response = $client->critique(['session_id' => 'test-session']);

        $this->assertFalse($response['success']);
        $this->assertSame(503, $response['status_code']);
    }
}
