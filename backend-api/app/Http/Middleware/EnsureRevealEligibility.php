<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRevealEligibility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->route('sessionId') ?? $request->input('sessionId');

        if ($sessionId) {
            // TODO: Implement when Session model exists
            // $session = Session::findOrFail($sessionId);
            // if ($session->state !== 'DONE') {
            //     abort(403, 'Session must be completed before revealing solutions.');
            // }
            // if (!$session->allTestsPassed()) {
            //     abort(403, 'All tests must pass before revealing solutions.');
            // }
        }

        return $next($request);
    }
}
