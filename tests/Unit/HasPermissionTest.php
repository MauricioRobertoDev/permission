<?php

use MrDev\Permission\Expections\PermissionDoesNotExistException;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Tests\User;

// hasPermission(string $permission): bool
test('Deve retornar se o usuário tem ou não determinada permissão', function () {
    config()->set('auth.defaults.guard', 'web');

    $user = User::query()->create(['email' => 'user@test.com']);
    $permission1 = Permission::create(['key' => 'test-permission-1']);
    $permission2 = Permission::create(['key' => 'test-permission-2']);

    expect($user->permissions()->count())->toBe(0);

    $user->permissions()->attach($permission1);

    expect($user->hasPermission($permission1))->toBeTrue();
    expect($user->hasPermission($permission1->id))->toBeTrue();
    expect($user->hasPermission($permission1->key))->toBeTrue();
    expect($user->hasPermission($permission2))->toBeFalse();
    expect($user->hasPermission('permission-does-not-exist'))->toBeFalse();
});

// addPermission(string $permission): self
test('Deve adicionar uma permissão para um usuário', function () {
    config()->set('auth.defaults.guard', 'web');

    $user = User::query()->create(['email' => 'user@test.com']);
    $permission1 = Permission::create(['key' => 'test-permission-1']);
    $permission2 = Permission::create(['key' => 'test-permission-2']);
    $permission3 = Permission::create(['key' => 'test-permission-3']);

    expect($user->permissions()->count())->toBe(0);

    $user->addPermission($permission1);
    $user->addPermission($permission2->id);
    $user->addPermission($permission3->key);

    expect($user->permissions()->count())->toBe(3);

    expect($user->hasPermission($permission1))->toBeTrue();
    expect($user->hasPermission($permission2))->toBeTrue();
    expect($user->hasPermission($permission3))->toBeTrue();
});

// addPermission(string $permission): self - ERROR
test('Deve retornar um erro ao tentar adicionar um permissão que não existe para um usuário', function () {
    config()->set('auth.defaults.guard', 'web');

    $user = User::query()->create(['email' => 'user@test.com']);

    expect(fn () => $user->addPermission('permission-does-not-exist'))->toThrow(PermissionDoesNotExistException::class);
});

// removePermission(Permission|string|int $permission, $guardName = null): void
test('Deve remover uma permissão de um usuário', function () {
    config()->set('auth.defaults.guard', 'web');

    $user = User::query()->create(['email' => 'user@test.com']);
    $permission = Permission::create(['key' => 'test-permission']);

    $user->addPermission($permission);

    expect($user->hasPermission($permission))->toBeTrue();

    $user->removePermission($permission);

    expect($user->hasPermission($permission))->toBeFalse();
});
