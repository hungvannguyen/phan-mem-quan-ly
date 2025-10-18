<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards): Response|RedirectResponse|JsonResponse
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Nếu request expect JSON, trả về JSON response
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Already authenticated.'], 200);
                }
                return redirect('/');
            }
        }

        return $next($request);
    }
}
