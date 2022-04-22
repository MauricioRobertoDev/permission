<?php

namespace MrDev\Permission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $permissions = explode('|', $permission);

        foreach ($permissions as $permission) {
            if ($request->user()->can($permission)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
