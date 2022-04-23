<?php

namespace MrDev\Permission\Middleware;

use Closure;
use MrDev\Permission\Expections\UnauthorizedException;
use MrDev\Permission\Models\Permission;

class CheckPermission
{
    public function handle($request, Closure $next, $permissions, string $guard = null)
    {
        $authGuard = app('auth')->guard($guard);

        // $guard = $authGuard->getDefaultDriver();

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $denied_permissions = [];

        $permissions = is_array($permissions)
            ? $permissions
            : explode('|', $permissions);

        foreach ($permissions as $permission) {
            if (config('app.debug')) {
                Permission::getPermissionOrFail($permission);
            }

            if ($authGuard->user()->can($permission)) {
                return $next($request);
            }

            $denied_permissions[] = $permission;
        }

        throw UnauthorizedException::forPermissions($denied_permissions);
    }
}
