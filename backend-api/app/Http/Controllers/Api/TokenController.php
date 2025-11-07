<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\LogsAuthEvents;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class TokenController extends Controller
{
    use LogsAuthEvents;

    /**
     * Display a listing of the user's tokens.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->tokens()->get()->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toDateTimeString(),
                    'created_at' => $token->created_at->toDateTimeString(),
                    'expires_at' => $token->expires_at?->toDateTimeString(),
                ];
            })
        );
    }

    /**
     * Store a newly created token in storage.
     */
    public function store(TokenRequest $request): JsonResponse
    {
        $expiresAt = $request->expires_at
            ? Carbon::parse($request->expires_at)
            : null;

        $token = $request->user()->createToken(
            $request->name,
            $request->abilities ?? ['*'],
            $expiresAt
        );

        $this->logAuthEvent($request, 'token_created', [
            'token_id' => $token->accessToken->id,
            'name' => $request->name,
        ]);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_id' => $token->accessToken->id,
            'name' => $token->accessToken->name,
            'abilities' => $token->accessToken->abilities,
            'expires_at' => $token->accessToken->expires_at?->toDateTimeString(),
        ], 201);
    }

    /**
     * Remove the specified token from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $token = $request->user()->tokens()->where('id', $id)->first();

        if (!$token) {
            return response()->json(['message' => 'Token not found'], 404);
        }

        $this->logAuthEvent($request, 'token_revoked', [
            'token_id' => $token->id,
            'name' => $token->name,
        ]);

        $token->delete();

        return response()->json(['message' => 'Token revoked successfully']);
    }
}
