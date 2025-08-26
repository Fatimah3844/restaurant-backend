<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class checkUserId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->input('user_id') ?? $request->input('customer_id');
        Log::info('Extracted user_id: ' . $userId);
        if (!$userId) {
            return response()->json(['message' => 'User ID is required.'], 401);
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        $request->merge(['user' => $user]);

        return $next($request);
    }
}
