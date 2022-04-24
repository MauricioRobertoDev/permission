<?php

namespace MrDev\Permission\Middleware;

use Closure;
use MrDev\Permission\Expections\UnauthorizedException;
use MrDev\Permission\Models\Role;

class CheckRole
{
    public function handle($request, Closure $next, $roles, string $guard = null)
    {
        $authGuard = app('auth')->guard($guard);

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $denied_roles = [];

        $roles = is_array($roles)
            ? $roles
            : explode('|', $roles);

        foreach ($roles as $role) {
            if (config('app.debug')) {
                Role::getPermissionOrFail($role);
            }

            if ($authGuard->user()->hasRole($role)) {
                return $next($request);
            }

            $denied_roles[] = $role;
        }

        throw UnauthorizedException::forGroups($denied_roles);
    }
}
