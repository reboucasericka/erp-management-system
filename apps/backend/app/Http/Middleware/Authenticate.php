<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

final class Authenticate extends Middleware
{
    /**
     * For API/Sanctum routes we never redirect to a login page.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }

        if (! Route::has('login')) {
            return null;
        }

        return route('login');
    }
}
