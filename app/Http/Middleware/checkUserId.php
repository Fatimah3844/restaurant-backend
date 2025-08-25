<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class checkUserId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
{
    $userId = $request->input('customer_id'); 
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
