<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $permissions = explode('|', $permission);
        abort_unless(collect($permissions)->contains(fn (string $candidate): bool => $request->user()?->hasPermission($candidate)), 403);

        return $next($request);
    }
}
