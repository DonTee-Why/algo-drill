<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionOwnership
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
            // if ($session->user_id !== $request->user()->id) {
            //     abort(403, 'You do not own this session.');
            // }
        }

        return $next($request);
    }
}
