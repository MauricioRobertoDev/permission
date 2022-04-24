<?php

use Illuminate\Support\Facades\Route;
use MrDev\Permission\Models\Role;
use MrDev\Permission\Tests\User;
use function Pest\Laravel\actingAs;

test('Deve retornar um erro ao setar uma role que não existe em uma rota', function () {
    Route::get('test', fn () => 'test')->middleware(['auth', 'role:does-not-exists']);

    $user = User::create(['email' => 'user@test.com']);

    actingAs($user)->get('test')->assertStatus(500);
});

test('Uma mesma role com guard diferente não deve permitir a passagem', function () {
    // os guard default api e web cobrem o model de usuário
    $user = User::create(['email' => 'user@test.com']);

    $r1 = Role::create(['key' => 'admin-role']); // guard default atual -> 'default'
    $r2 = Role::create(['key' => 'admin-role', 'guard_name' => 'api']);
    $r3 = Role::create(['key' => 'admin-role', 'guard_name' => 'web']);

    Route::get('test-x-1', fn () => 'test')->middleware(['auth', 'role:admin-role']); // guard default atual -> 'default'
    Route::get('test-x-2', fn () => 'test')->middleware(['auth:api', 'role:admin-role']);
    Route::get('test-x-3', fn () => 'test')->middleware(['auth:web', 'role:admin-role']);

    // como estou fazendo login com diferentes guards, apartir do primeiro guard diferente do default,
    // eu preciso fazer login com o guard desejado,
    // para que o middleware de role funcione corretamente
    // pois o guard default muda para o guard do último login

    // usando guard default que é 'default'
    actingAs($user)->get('test-x-1')->assertForbidden();
    // quando essta linha executar, o guard default será 'api'
    actingAs($user, 'api')->get('test-x-2')->assertForbidden();
    // quando essta linha executar, o guard default será 'web'
    actingAs($user, 'web')->get('test-x-3')->assertForbidden();

    $user->addRole($r3);

    // agora preciso especificar o guard default para 'default'
    // caso contrario ele fala login com o guard default que atualmente é 'web'
    // e irá checar se o usuário tem uma permissão com key 'admin-role' e guard 'web'
    actingAs($user, 'default')->get('test-x-1')->assertForbidden();
    actingAs($user, 'api')->get('test-x-2')->assertForbidden();
    actingAs($user, 'web')->get('test-x-3')->assertSuccessful();

    $user->addRole($r1);
    $user->addRole($r2);

    actingAs($user, 'default')->get('test-x-1')->assertSuccessful();
    actingAs($user, 'api')->get('test-x-2')->assertSuccessful();
    actingAs($user, 'web')->get('test-x-3')->assertSuccessful();
});
