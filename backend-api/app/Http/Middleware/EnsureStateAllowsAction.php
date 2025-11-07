<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStateAllowsAction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->route('sessionId') ?? $request->input('sessionId');
        $requiredState = $request->route('state') ?? $request->input('requiredState');

        if ($sessionId && $requiredState) {
            // TODO: Implement when Session model exists
            // $session = Session::findOrFail($sessionId);
            // if ($session->state !== $requiredState) {
            //     abort(403, 'Session state does not allow this action.');
            // }
        }

        return $next($request);
    }
}
