<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        Gate::authorize('accessAdminArea', User::class);

        return $next($request);
    }
}
