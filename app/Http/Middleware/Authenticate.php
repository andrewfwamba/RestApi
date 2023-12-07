<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request)
    {
        if ($request->expectsJson()) {
            throw new AuthenticationException('Unauthenticated.', [response()->json(['success' => false, 'message' => 'Invalid token'], 401)]);
        }

        return null;
        // return $request->expectsJson() ? null : response()->json(['success' => false, "message" => "Invalid token"], 401);
    }
}
