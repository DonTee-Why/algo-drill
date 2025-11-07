<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EmailVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class HandleEmailVerified implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(EmailVerified $event): void
    {
        // Add any queued logic here when email is verified
        // For example: send welcome email, update analytics, etc.
    }
}
