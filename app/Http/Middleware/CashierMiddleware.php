<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CashierMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user;

        if (!$user || ($user->role !== 'cashier' && $user->role !== 'admin')) {
            return response()->json(['message' => 'Unauthorized. Cashiers or Admins only.'], 403);
        }

        return $next($request);
    }
}

//م