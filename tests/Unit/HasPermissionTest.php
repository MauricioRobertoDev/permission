<?php

use Illuminate\Support\Facades\Cache;
use MrDev\Permission\Expections\GuardDoesNotExists;
use MrDev\Permission\Expections\GuardDoesNotMatch;
use MrDev\Permission\Expections\PermissionDoesNotExistException;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Models\Role;
use MrDev\Permission\Tests\User;

// hasPermission(string $permission): bool
test('Deve retornar se o usuário tem ou não determinada permissão', function () {
    $user = User::create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    $permission1 = Permission::create(['key' => 'test-permission-1']);
    $permission2 = Permission::create(['key' => 'test-permission-2']);

    expect($user->permissions()->count())->toBe(0);
    expect($role->permissions()->count())->toBe(0);

    $user->permissions()->attach($permission1);
    $role->permissions()->attach($permission1);

    expect($user->permissions()->count())->toBe(1);
    expect($role->permissions()->count())->toBe(1);

    expect($user->hasPermission($permission1))->toBeTrue();
    expect($user->hasPermission($permission1->id))->toBeTrue();
    expect($user->hasPermission($permission1->key))->toBeTrue();
    expect($user->hasPermission($permission2))->toBeFalse();
    expect(fn () => $user->hasPermission('permission-does-not-exist'))->toThrow(PermissionDoesNotExistException::class);

    expect($role->hasPermission($permission1))->toBeTrue();
    expect($role->hasPermission($permission1->id))->toBeTrue();
    expect($role->hasPermission($permission1->key))->toBeTrue();
    expect($role->hasPermission($permission2))->toBeFalse();
    expect(fn () => $role->hasPermission('permission-does-not-exist'))->toThrow(PermissionDoesNotExistException::class);
});

// addPermission(string $permission): self
test('Deve adicionar uma permissão para um usuário', function () {
    $user = User::query()->create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    $permission1 = Permission::create(['key' => 'test-permission-1']);
    $permission2 = Permission::create(['key' => 'test-permission-2']);
    $permission3 = Permission::create(['key' => 'test-permission-3']);

    expect($user->permissions()->count())->toBe(0);
    expect($role->permissions()->count())->toBe(0);

    $user->addPermission($permission1);
    $user->addPermission($permission2->id);
    $user->addPermission($permission3->key);

    $role->addPermission($permission1);
    $role->addPermission($permission2->id);
    $role->addPermission($permission3->key);

    expect($user->permissions()->count())->toBe(3);
    expect($user->hasPermission($permission1))->toBeTrue();
    expect($user->hasPermission($permission2))->toBeTrue();
    expect($user->hasPermission($permission3))->toBeTrue();


    expect($role->permissions()->count())->toBe(3);
    expect($role->hasPermission($permission1))->toBeTrue();
    expect($role->hasPermission($permission2))->toBeTrue();
    expect($role->hasPermission($permission3))->toBeTrue();
});

// addPermission(string $permission): self - ERROR
test('Deve retornar um erro ao tentar adicionar um permissão que não existe para um usuário', function () {
    $user = User::query()->create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    expect(fn () => $user->addPermission('permission-does-not-exist'))->toThrow(PermissionDoesNotExistException::class);
    expect(fn () => $role->addPermission('permission-does-not-exist'))->toThrow(PermissionDoesNotExistException::class);
});

// addPermission(string $permission): self - ERROR
test('Deve retornar um erro ao tentar aidicionar uma permissão que tem um guard que não existe', function () {
    $user = User::create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    $p = Permission::query()->create(['key' => 'test-permission', 'guard_name' => 'guard-does-not-exists']);

    expect(fn () => $user->addPermission($p))->toThrow(GuardDoesNotExists::class);
    expect(fn () => $role->addPermission($p))->toThrow(GuardDoesNotExists::class);
});

// addPermission(string $permission): self - ERROR
test('Deve retornar um erro ao tentar adicionar um permissão com um guard que não cobre o model/user', function () {
    $p1 = Permission::create(['key' => 'test-permission']); // default
    $p2 = Permission::create(['key' => 'test-permission', 'guard_name' => 'web']);
    $p3 = Permission::create(['key' => 'test-permission', 'guard_name' => 'admin']);
    $p4 = Permission::query()->create(['key' => 'test-permission-XXXXX', 'guard_name' => 'guard-does-not-exists']);

    $user = User::query()->create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    $user->addPermission($p1);
    $user->addPermission($p2);

    $role->addPermission($p1);


    expect(fn () => $user->addPermission($p3))->toThrow(GuardDoesNotMatch::class);
    expect(fn () => $user->addPermission($p4))->toThrow(GuardDoesNotExists::class);

    expect(fn () => $role->addPermission($p2))->toThrow(GuardDoesNotMatch::class);
    expect(fn () => $role->addPermission($p3))->toThrow(GuardDoesNotMatch::class);
    expect(fn () => $role->addPermission($p4))->toThrow(GuardDoesNotExists::class);
});

// removePermission(Permission|string|int $permission, $guardName = null): void
test('Deve remover uma permissão de um usuário', function () {
    $user = User::query()->create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    $permission = Permission::create(['key' => 'test-permission']);

    $user->addPermission($permission);
    $role->addPermission($permission);

    expect($user->hasPermission($permission))->toBeTrue();
    expect($role->hasPermission($permission))->toBeTrue();

    $user->removePermission($permission);
    $role->removePermission($permission);

    expect($user->hasPermission($permission))->toBeFalse();
    expect($role->hasPermission($permission))->toBeFalse();
});

//  getPermissions(): Collection
test('Deve recuperar a lista de permissões do model no storage', function () {
    $user = User::create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    $permission = Permission::create(['key' => 'test-permission']);

    expect($user->getPermissions()->count())->toBe(0);
    expect($role->getPermissions()->count())->toBe(0);

    $user->addPermission($permission);
    $role->addPermission($permission);

    expect($user->getPermissions()->count())->toBe(1);
    expect($user->getPermissions()->contains($permission))->toBeTrue();

    expect($role->getPermissions()->count())->toBe(1);
    expect($role->getPermissions()->contains($permission))->toBeTrue();
});

// getPermissions(): Collection - CACHE
test('Ao pegar as permissões deve criar um cache com as permissões', function () {
    $user = User::create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    $key1 = 'permissions::of::' . $user::class . '::' . $user->getKey();
    $key2 = 'permissions::of::' . $role::class . '::' . $role->getKey();

    expect(Cache::has($key1))->toBeFalse();
    expect(Cache::has($key2))->toBeFalse();

    $user->getPermissions();
    $role->getPermissions();

    expect(Cache::has($key1))->toBeTrue();
    expect(Cache::has($key2))->toBeTrue();
});

// refreshPermissions(): void
test('Deve esquecer o cache com as permissões do model', function () {
    $user = User::create(['email' => 'user@test.com']);
    $role = Role::create(['key' => 'role-test']);

    $key1 = 'permissions::of::' . $user::class . '::' . $user->getKey();
    $key2 = 'permissions::of::' . $role::class . '::' . $role->getKey();

    expect(Cache::has($key1))->toBeFalse();
    expect(Cache::has($key2))->toBeFalse();

    $user->getPermissions();
    $role->getPermissions();

    expect(Cache::has($key1))->toBeTrue();
    expect(Cache::has($key2))->toBeTrue();

    $user->refreshPermissions();
    $role->refreshPermissions();

    expect(Cache::has($key1))->toBeFalse();
    expect(Cache::has($key2))->toBeFalse();
});
