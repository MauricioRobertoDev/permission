<?php

use Illuminate\Support\Facades\Route;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Tests\Admin;
use MrDev\Permission\Tests\User;
use function Pest\Laravel\actingAs;

test('Deve retornar um erro ao setar uma permissão que não existe em uma rota', function () {
    Route::get('test', fn () => 'test')->middleware(['auth', 'permission:permission-does-not-exists']);

    $user = User::create(['email' => 'user@test.com']);

    actingAs($user, )->get('test')->assertStatus(500);
});

test('Uma mesma permission com guard diferente não deve permitir a passagem', function () {
    // os guard default api e web cobrem o model de usuário
    $user = User::create(['email' => 'user@test.com']);

    $p1 = Permission::create(['key' => 'admin-permission']); // guard default atual -> 'default'
    $p2 = Permission::create(['key' => 'admin-permission', 'guard_name' => 'api']);
    $p3 = Permission::create(['key' => 'admin-permission', 'guard_name' => 'web']);

    Route::get('test-x-1', fn () => 'test')->middleware(['auth', 'permission:admin-permission']); // guard default atual -> 'default'
    Route::get('test-x-2', fn () => 'test')->middleware(['auth:api', 'permission:admin-permission']);
    Route::get('test-x-3', fn () => 'test')->middleware(['auth:web', 'permission:admin-permission']);

    // como estou fazendo login com diferentes guards, apartir do primeiro guard diferente do default,
    // eu preciso fazer login com o guard desejado,
    // para que o middleware de permissão funcione corretamente
    // pois o guard default muda para o guard do último login

    // usando guard default que é 'default'
    actingAs($user)->get('test-x-1')->assertForbidden();
    // quando essta linha executar, o guard default será 'api'
    actingAs($user, 'api')->get('test-x-2')->assertForbidden();
    // quando essta linha executar, o guard default será 'web'
    actingAs($user, 'web')->get('test-x-3')->assertForbidden();

    $user->addPermission($p3);

    // agora preciso especificar o guard default para 'default'
    // caso contrario ele fala login com o guard default que atualmente é 'web'
    // e irá checar se o usuário tem uma permissão com key 'admin-permission' e guard 'web'
    actingAs($user, 'default')->get('test-x-1')->assertForbidden();
    actingAs($user, 'api')->get('test-x-2')->assertForbidden();
    actingAs($user, 'web')->get('test-x-3')->assertSuccessful();

    $user->addPermission($p1);
    $user->addPermission($p2);

    actingAs($user, 'default')->get('test-x-1')->assertSuccessful();
    actingAs($user, 'api')->get('test-x-2')->assertSuccessful();
    actingAs($user, 'web')->get('test-x-3')->assertSuccessful();
});
