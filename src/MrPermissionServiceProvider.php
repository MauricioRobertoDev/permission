<?php

namespace MrDev\Permission;

use MrDev\Permission\Commands\PermissionCommand;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Observers\PermissionObserver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MrPermissionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
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
        //]
        Permission::observe(PermissionObserver::class);
    }

    public function packageBooted()
    {
        //
    }
}
