<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MrDev\Permission\Expections\GuardDoesNotExists;
use MrDev\Permission\Expections\PermissionDoesNotExistException;
use MrDev\Permission\Models\Permission;

// create(array $attributes = []): self
test('Deve criar uma permissão e adicionar automaticamente o guard padrão', function () {
    $permission1 = Permission::create(['key' => 'test-permission']);
    $permission2 = Permission::create(['key' => 'test-permission', 'guard_name' => 'api']);

    expect($permission1->guard_name)->toBe('default');
    expect($permission2->guard_name)->toBe('api');
});

// create(array $attributes = []): self - ERROR
test('Deve retornar um erro criar uma permissão com um guard que não existe', function () {
    expect(fn () => Permission::create(['key' => 'test-permission', 'guard_name' => 'guard-does-not-exists']))->toThrow(GuardDoesNotExists::class);
});

// findById(string $id, string $guardName = null): Permission
test('Deve recuperar uma permissão do storage pelo id', function () {
    $permission1 = Permission::create(['key' => 'test-permission-1']);
    $permission2 = Permission::create(['key' => 'test-permission-2']);

    $permission = Permission::findById($permission2->id);

    expect($permission->id)->toBe(2);
    expect($permission->key)->toBe('test-permission-2');
});

// findById(string $id, string $guardName = null): Permission - ERROR
test('Deve retornar um erro ao tentar recuperar pelo id uma permissão que não existe do storage', function () {
    expect(fn () => Permission::findById(999))->toThrow(PermissionDoesNotExistException::class);
});

// findByKey(string $key, string $guardName = null): Permission
test('Deve recuperar uma permissão do storage pela key', function () {
    $permission1 = Permission::create(['key' => 'test-permission-1']);
    $permission2 = Permission::create(['key' => 'test-permission-2']);

    $permission = Permission::findByKey($permission2->key);

    expect($permission->id)->toBe(2);
    expect($permission->key)->toBe('test-permission-2');
});

// findByKey(string $key, string $guardName = null): Permission - ERROR
test('Deve retornar um erro ao tentar recuperar pela key uma permissão que não existe do storage', function () {
    expect(fn () => Permission::findByKey('permission-does-not-exist'))->toThrow(PermissionDoesNotExistException::class);
});

// getPermission(Permission|string|int $permission, string $guard): Permission|null
test('Deve pegar uma permissão do storage e retornar null caso não exista', function () {
    $permission = Permission::create(['key' => 'test-permission']);
    $permissionWeb = Permission::create(['key' => 'test-permission-x', 'guard_name' => 'web']);
    $permissionApi = Permission::create(['key' => 'test-permission-x', 'guard_name' => 'api']);

    $permission = Permission::getPermission($permission);
    expect($permission->key)->toBe('test-permission');
    expect($permission->guard_name)->toBe('default');

    $permission = Permission::getPermission($permissionWeb, 'web');
    expect($permission->key)->toBe('test-permission-x');
    expect($permission->guard_name)->toBe('web');

    $permission = Permission::getPermission($permissionApi, 'api');
    expect($permission->key)->toBe('test-permission-x');
    expect($permission->guard_name)->toBe('api');

    $permission = Permission::getPermission(999);
    expect($permission)->toBeNull();
});

// getPermissionOrFail(Permission|string|int $permission, string $guardName = null): Permission
test('Deve pegar uma permissão do storage e retornar um erro caso não exista', function () {
    $permission = Permission::create(['key' => 'test-permission']);
    $permissionWeb = Permission::create(['key' => 'test-permission-x', 'guard_name' => 'web']);
    $permissionApi = Permission::create(['key' => 'test-permission-x', 'guard_name' => 'api']);

    $permission = Permission::getPermissionOrFail($permission);
    expect($permission->key)->toBe('test-permission');
    expect($permission->guard_name)->toBe('default');

    $permission = Permission::getPermissionOrFail($permissionWeb, 'web');
    expect($permission->key)->toBe('test-permission-x');
    expect($permission->guard_name)->toBe('web');

    $permission = Permission::getPermissionOrFail($permissionApi, 'api');
    expect($permission->key)->toBe('test-permission-x');
    expect($permission->guard_name)->toBe('api');

    expect(fn () => Permission::getPermissionOrFail(999))->toThrow(PermissionDoesNotExistException::class);
});

// getAllPermissions(): Collection
test('Deve retornar todas as permissões', function () {
    $permission = Permission::create(['key' => 'test-permission']);
    $permission1 = Permission::create(['key' => 'test-permission-1']);
    $permission2 = Permission::create(['key' => 'test-permission-2']);

    expect($permission->getAllPermissions())->toBeInstanceOf(Collection::class);
    expect($permission->getAllPermissions()->count())->toBe(3);
    expect($permission->getAllPermissions()->contains($permission))->toBeTrue();
    expect($permission->getAllPermissions()->contains($permission1))->toBeTrue();
    expect($permission->getAllPermissions()->contains($permission2))->toBeTrue();
});

// getAllPermissions(): Collection - CACHE
test('Ao pegar todas as permissões deve criar um cache todas as permissões', function () {
    $permission = Permission::create(['key' => 'test-permission']);

    expect(Cache::has('permissions::all'))->toBeFalse();

    $permission->getAllPermissions();

    expect(Cache::has('permissions::all'))->toBeTrue();
});

// refreshPermissions(): void
test('Deve esquecer o cache de permissões', function () {
    $permission = Permission::create(['key' => 'test-permission']);

    expect(Cache::has('permissions::all'))->toBeFalse();

    $permission->getAllPermissions();

    expect(Cache::has('permissions::all'))->toBeTrue();

    $permission->refreshPermissions();

    expect(Cache::has('permissions::all'))->toBeFalse();
});

// exists(string $key, string $guardName = null): bool
test('Deve retornar se uma permissão existe ou não', function () {
    $permission = Permission::create(['key' => 'test-permission']);
    expect(Permission::exists($permission->key))->toBeTrue();

    $permissionDeleted = Permission::create(['key' => 'test-permission-deleted']);
    expect(Permission::exists($permissionDeleted->key))->toBeTrue();
    $permissionDeleted->delete();
    expect(Permission::exists($permissionDeleted->key))->toBeFalse();

    expect(Permission::exists('does not exist permission'))->toBeFalse();
});
