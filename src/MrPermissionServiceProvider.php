<?php

namespace MrDev\Permission;

use Illuminate\Contracts\Auth\Access\Authorizable;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Routing\Router;
use MrDev\Permission\Commands\PermissionCommand;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Observers\PermissionObserver;
use MrDev\Permission\Traits\HasPermissions;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MrPermissionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('permission')
            ->hasConfigFile()
            ->hasMigration('create_permission_table')
            ->hasCommand(PermissionCommand::class);
    }

    public function packageRegistered()
    {
        app()->singleton(MrPermission::class, fn ($app) => new MrPermission());
        app()->bind('mr-permission', MrPermission::class);
    }

    public function bootingPackage()
    {
        Permission::observe(PermissionObserver::class);


        app(Gate::class)->before(function (Authorizable $user, string $ability) {
            if (Permission::exists($ability) && method_exists($user, 'hasPermission')) {
                /** @var HasPermissions $user */
                return $user->hasPermission($ability);
            }
        });

        $router = app(Router::class);
        $router->aliasMiddleware('permission', \MrDev\Permission\Middleware\CheckPermission::class);
    }

    public function packageBooted()
    {
        //
    }
}
