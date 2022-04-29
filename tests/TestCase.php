<?php

namespace MrDev\Permission\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use MrDev\Permission\MrPermissionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'MrDev\\Permission\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            MrPermissionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.debug', true);

        config()->set('auth.defaults.guard', 'default');
        config()->set('auth.guards.web', ['driver' => 'session', 'provider' => 'users']);
        config()->set('auth.guards.default', ['driver' => 'session', 'provider' => 'users']);
        config()->set('auth.guards.api', ['driver' => 'session', 'provider' => 'users']);
        config()->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admins']);
        config()->set('auth.providers.users', ['driver' => 'eloquent', 'model' => User::class]);
        config()->set('auth.providers.admins', ['driver' => 'eloquent', 'model' => Admin::class]);

        app('db')->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        app('db')->connection()->getSchemaBuilder()->create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        $migration = include __DIR__ . '/../database/migrations/create_permission_tables.php';
        $migration->up();
    }
}
