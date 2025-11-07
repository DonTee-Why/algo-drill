<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EmailVerified;
use App\Mail\WelcomeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

final class HandleEmailVerified implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(EmailVerified $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
}
