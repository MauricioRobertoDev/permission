<?php

use MrDev\Permission\Expections\PermissionDoesNotExistException;
use MrDev\Permission\Models\Permission;

// create(array $attributes = []): self
test('Deve criar uma permissão e adicionar automaticamente o guard padrão', function () {
    $permission1 = Permission::create(['key' => 'test-permission']);
    $permission2 = Permission::create(['key' => 'test-permission', 'guard_name' => 'test']);

    expect($permission1->guard_name)->toBe('default');
    expect($permission2->guard_name)->toBe('test');
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

    $permission = Permission::getPermission($permissionWeb);
    expect($permission->key)->toBe('test-permission-x');
    expect($permission->guard_name)->toBe('web');

    $permission = Permission::getPermission($permissionApi);
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

    $permission = Permission::getPermissionOrFail($permissionWeb);
    expect($permission->key)->toBe('test-permission-x');
    expect($permission->guard_name)->toBe('web');

    $permission = Permission::getPermissionOrFail($permissionApi);
    expect($permission->key)->toBe('test-permission-x');
    expect($permission->guard_name)->toBe('api');

    expect(fn () => Permission::getPermissionOrFail(999))->toThrow(PermissionDoesNotExistException::class);
});
