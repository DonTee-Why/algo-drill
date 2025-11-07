<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\AuthEvent;
use Illuminate\Http\Request;

trait LogsAuthEvents
{
    protected function logAuthEvent(Request $request, string $eventType, array $metadata = []): void
    {
        AuthEvent::create([
            'user_id' => $request->user()?->id,
            'event_type' => $eventType,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
