<?php

use Illuminate\Support\Facades\Route;
use MrDev\Permission\Models\Permission;

use MrDev\Permission\Tests\Admin;
use MrDev\Permission\Tests\User;
use function Pest\Laravel\actingAs;

test('Deve retornar um erro ao setar uma permissão que não existe em uma rota caso app_debug seja true', function () {
    Route::get('test', fn () => 'test')->middleware(['auth', 'permission:test']);

    $user = User::create(['email' => 'user@test.com']);

    config()->set('app.debug', true);
    actingAs($user, )->get('test')->assertStatus(500);

    config()->set('app.debug', false);
    actingAs($user, )->get('test')->assertForbidden();
});

test('Deve garantir que o usuário tenha a permissão msm com guards diferentes', function () {
    $px1 = Permission::create(['key' => 'admin-permission-x', 'guard_name' => 'admin']);
    $pz1 = Permission::create(['key' => 'admin-permission-z', 'guard_name' => 'web']);

    Route::get('test-admin-x', fn () => 'test')->middleware(['auth:admin', 'permission:admin-permission-x']);
    Route::get('test-user-z', fn () => 'test')->middleware(['auth:web', 'permission:admin-permission-z']);

    $admin = Admin::create(['email' => 'admin@test.com']);
    actingAs($admin, 'admin')->get('test-admin-x')->assertForbidden();
    $admin->addPermission($px1);
    actingAs($admin, 'admin')->get('test-admin-x')->assertOk();


    $user = User::create(['email' => 'user@test.com']);
    actingAs($user, 'web')->get('test-user-z')->assertForbidden();
    $user->addPermission($pz1);
    actingAs($user, 'web')->get('test-user-z')->assertOk();
});
