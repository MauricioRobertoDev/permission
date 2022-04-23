<?php

// create(array $attributes = []): self

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MrDev\Permission\Expections\GuardDoesNotExists;
use MrDev\Permission\Expections\RoleDoesNotExistException;
use MrDev\Permission\Models\Role;

// create(array $attributes = []): self
test('Deve criar uma role e adicionar automaticamente o guard padrão', function () {
    $role1 = Role::create(['key' => 'test-role']);
    $role2 = Role::create(['key' => 'test-role', 'guard_name' => 'api']);

    expect($role1->guard_name)->toBe('default');
    expect($role2->guard_name)->toBe('api');
});

// create(array $attributes = []): self - ERROR
test('Deve retornar um erro criar uma role com um guard que não existe', function () {
    expect(fn () => Role::create(['key' => 'test-role', 'guard_name' => 'guard-does-not-exists']))->toThrow(GuardDoesNotExists::class);
});

// findById(string $id, string $guardName = null): Role
test('Deve recuperar uma role do storage pelo id', function () {
    Role::create(['key' => 'test-role-1']);
    $role = Role::create(['key' => 'test-role-2']);

    $role = Role::findById($role->id);

    expect($role->id)->toBe(2);
    expect($role->key)->toBe('test-role-2');
});

// findById(string $id, string $guardName = null): Role - ERROR
test('Deve retornar um erro ao tentar recuperar pelo id uma role que não existe do storage', function () {
    expect(fn () => Role::findById(999))->toThrow(RoleDoesNotExistException::class);
});

// findByKey(string $key, string $guardName = null): Role
test('Deve recuperar uma role do storage pela key', function () {
    Role::create(['key' => 'test-role-1']);
    $role = Role::create(['key' => 'test-role-2']);

    $role = Role::findByKey($role->key);

    expect($role->id)->toBe(2);
    expect($role->key)->toBe('test-role-2');
});

// findByKey(string $key, string $guardName = null): Role - ERROR
test('Deve retornar um erro ao tentar recuperar pela key uma permissão que não existe do storage', function () {
    expect(fn () => Role::findByKey('role-does-not-exist'))->toThrow(RoleDoesNotExistException::class);
});

// getRole(Role|string|int $role, string $guard): Role|null
test('Deve pegar uma permissão do storage e retornar null caso não exista', function () {
    $role = Role::create(['key' => 'test-role']);
    $roleWeb = Role::create(['key' => 'test-role-x', 'guard_name' => 'web']);
    $roleApi = Role::create(['key' => 'test-role-x', 'guard_name' => 'api']);

    $role = Role::getRole($role);
    expect($role->key)->toBe('test-role');
    expect($role->guard_name)->toBe('default');

    $role = Role::getRole($roleWeb, 'web');
    expect($role->key)->toBe('test-role-x');
    expect($role->guard_name)->toBe('web');

    $role = Role::getRole($roleApi, 'api');
    expect($role->key)->toBe('test-role-x');
    expect($role->guard_name)->toBe('api');

    $role = Role::getRole(999);
    expect($role)->toBeNull();
});

// getRoleOrFail(Role|string|int $role, string $guardName = null): Role
test('Deve pegar uma role do storage e retornar um erro caso não exista', function () {
    $role = Role::create(['key' => 'test-role']);
    $roleWeb = Role::create(['key' => 'test-role-x', 'guard_name' => 'web']);
    $roleApi = Role::create(['key' => 'test-role-x', 'guard_name' => 'api']);

    $role = Role::getRoleOrFail($role);
    expect($role->key)->toBe('test-role');
    expect($role->guard_name)->toBe('default');

    $role = Role::getRoleOrFail($roleWeb, 'web');
    expect($role->key)->toBe('test-role-x');
    expect($role->guard_name)->toBe('web');

    $role = Role::getRoleOrFail($roleApi, 'api');
    expect($role->key)->toBe('test-role-x');
    expect($role->guard_name)->toBe('api');

    expect(fn () => Role::getRoleOrFail(999))->toThrow(RoleDoesNotExistException::class);
});

// getAllRoles(): Collection
test('Deve retornar todas as roles', function () {
    $role = Role::create(['key' => 'test-role']);
    $role1 = Role::create(['key' => 'test-role-1']);
    $role2 = Role::create(['key' => 'test-role-2']);

    expect($role->getAllRoles())->toBeInstanceOf(Collection::class);
    expect($role->getAllRoles()->count())->toBe(3);
    expect($role->getAllRoles()->contains($role))->toBeTrue();
    expect($role->getAllRoles()->contains($role1))->toBeTrue();
    expect($role->getAllRoles()->contains($role2))->toBeTrue();
});

// getAllRoles(): Collection
test('Ao pegar todas as roles deve criar um cache todas as roles', function () {
    $role = Role::create(['key' => 'test-role']);

    expect(Cache::has('mrdev::cache::roles::all'))->toBeFalse();

    $role->getAllRoles();

    expect(Cache::has('mrdev::cache::roles::all'))->toBeTrue();
});

// refreshStorage(): void
test('Deve esquecer o cache de roles', function () {
    $key = 'mrdev::cache::roles::all';

    expect(Cache::has($key))->toBeFalse();

    Role::getAllroles();

    expect(Cache::has($key))->toBeTrue();

    Role::refreshStorage();

    expect(Cache::has($key))->toBeFalse();
});

// exists(string $key, string $guardName = null): bool
test('Deve retornar se uma permissão existe ou não', function () {
    $role = Role::create(['key' => 'test-role']);
    expect(Role::exists($role->key))->toBeTrue();

    $roleDeleted = Role::create(['key' => 'test-role-deleted']);
    expect(Role::exists($roleDeleted->key))->toBeTrue();
    $roleDeleted->delete();
    expect(Role::exists($roleDeleted->key))->toBeFalse();

    expect(Role::exists('role-does-not-exists'))->toBeFalse();
});
