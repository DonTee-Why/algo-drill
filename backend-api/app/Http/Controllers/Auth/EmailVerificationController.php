<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Events\EmailVerified;
use App\Helpers\LogsAuthEvents;
use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

final class EmailVerificationController extends Controller
{
    use LogsAuthEvents;

    /**
     * Display the email verification prompt.
     */
    public function prompt(Request $request): Response|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard'))
                    : Inertia::render('Auth/VerifyEmail');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        if ($user->getKey() != $id) {
            abort(403);
        }

        if (! hash_equals(sha1($user->email), $hash)) {
            abort(403);
        }

        if ($user->markEmailAsVerified()) {
            event(new EmailVerified($user));
            $this->logAuthEvent($request, 'email_verified');
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Send a new email verification notification.
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        Mail::to($user->email)->send(new EmailVerificationNotification($user));

        return back()->with('status', 'verification-link-sent');
    }
}
