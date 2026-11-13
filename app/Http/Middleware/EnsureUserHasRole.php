<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Usage: ->middleware('role:admin,professor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Votre compte est inactif.');
        }

        $allowed = array_filter(array_map(
            fn (string $role) => UserRole::fromName($role),
            $roles
        ));

        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'Vous n’avez pas accès à cet espace.');
        }

        return $next($request);
    }
}
