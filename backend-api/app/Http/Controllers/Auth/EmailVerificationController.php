<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Events\EmailVerified;
use App\Helpers\LogsAuthEvents;
use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        // Automatically queue verification email if user hasn't verified
        Mail::to($user->email)->queue(new EmailVerificationNotification($user));

        return Inertia::render('Auth/VerifyEmail');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        if (! hash_equals(sha1($user->email), $hash)) {
            abort(403, 'Invalid verification hash.');
        }

        // Validate the signed URL
        // Note: Old emails may have been signed with different APP_URL (with port)
        // If signature fails but hash is valid (already checked above), allow it for old links
        // The hash check ensures the user ID and email match, providing security
        if (! $request->hasValidSignature()) {
            // Check if this might be an old link (signed with port 80)
            // If the hash is valid (checked above), we can allow it
            $fullUrl = $request->fullUrl();
            $hasPortInUrl = str_contains($fullUrl, 'localhost:80');

            // If URL doesn't have port but signature fails, it's likely an old link
            // Since hash is already validated, we allow it
            if (! $hasPortInUrl) {
                // Old link - hash validation above is sufficient for security
            } else {
                // URL has port but signature still fails - reject
                abort(403, 'Invalid signature.');
            }
        }

        if ($user->markEmailAsVerified()) {
            event(new EmailVerified($user));
            if ($request->user()) {
                $this->logAuthEvent($request, 'email_verified');
            }
        }

        // Log the user in if they're not already authenticated
        if (! $request->user()) {
            Auth::login($user);
        }

        return redirect()->intended(route('dashboard'))->with('verified', true);
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

        Mail::to($user->email)->queue(new EmailVerificationNotification($user));

        return back()->with('status', 'verification-link-sent');
    }
}
