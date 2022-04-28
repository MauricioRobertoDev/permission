<?php

use Illuminate\Support\Facades\Cache;
use MrDev\Permission\Expections\GuardDoesNotExists;
use MrDev\Permission\Expections\GuardDoesNotMatch;
use MrDev\Permission\Expections\RoleDoesNotExistException;
use MrDev\Permission\Models\Role;
use MrDev\Permission\Tests\User;

// hasRole(string $role): bool
test('Deve retornar se o usuário tem ou não determinada role', function () {
    $user = User::create(['email' => 'user@test.com']);

    $role1 = Role::create(['key' => 'test-role-1']);
    $role2 = Role::create(['key' => 'test-role-2']);

    expect($user->roles()->count())->toBe(0);

    $user->roles()->attach($role1);

    expect($user->roles()->count())->toBe(1);

    expect($user->hasRole($role1))->toBeTrue();
    expect($user->hasRole($role1->id))->toBeTrue();
    expect($user->hasRole($role1->key))->toBeTrue();
    expect($user->hasRole($role2))->toBeFalse();
    expect(fn () => $user->hasRole('role-does-not-exist'))->toThrow(RoleDoesNotExistException::class);
});

// addRole(string $role): self
test('Deve adicionar uma role para um usuário', function () {
    $user = User::query()->create(['email' => 'user@test.com']);

    $role1 = Role::create(['key' => 'test-role-1']);
    $role2 = Role::create(['key' => 'test-role-2']);
    $role3 = Role::create(['key' => 'test-role-3']);

    expect($user->roles()->count())->toBe(0);

    $user->addRole($role1);
    $user->addRole($role2->id);
    $user->addRole($role3->key);

    expect($user->roles()->count())->toBe(3);
    expect($user->hasRole($role1))->toBeTrue();
    expect($user->hasRole($role2))->toBeTrue();
    expect($user->hasRole($role3))->toBeTrue();
});

// addRole(string $role): self - ERROR
test('Deve retornar um erro ao tentar adicionar um role que não existe para um usuário', function () {
    $user = User::query()->create(['email' => 'user@test.com']);

    expect(fn () => $user->addRole('role-does-not-exist'))->toThrow(RoleDoesNotExistException::class);
});

// addRole(string $role): self - ERROR
test('Deve retornar um erro ao tentar aidicionar uma role que tem um guard que não existe', function () {
    $user = User::create(['email' => 'user@test.com']);

    $r = Role::query()->create(['key' => 'test-role', 'guard_name' => 'guard-does-not-exists']);

    expect(fn () => $user->addRole($r))->toThrow(GuardDoesNotExists::class);
});

// addRole(string $role): self - ERROR
test('Deve retornar um erro ao tentar adicionar uma role com um guard que não cobre o model/user', function () {
    $r1 = Role::create(['key' => 'test-role']); // default
    $r2 = Role::create(['key' => 'test-role', 'guard_name' => 'web']);
    $r3 = Role::create(['key' => 'test-role', 'guard_name' => 'admin']);
    $r4 = Role::query()->create(['key' => 'test-role-XXXXX', 'guard_name' => 'guard-does-not-exists']);

    $user = User::query()->create(['email' => 'user@test.com']);

    $user->addRole($r1);
    $user->addRole($r2);

    expect(fn () => $user->addRole($r3))->toThrow(GuardDoesNotMatch::class);
    expect(fn () => $user->addRole($r4))->toThrow(GuardDoesNotExists::class);
});

// removeRole(Role|string|int $role, $guardName = null): void
test('Deve remover uma role de um usuário', function () {
    $user = User::query()->create(['email' => 'user@test.com']);

    $role = Role::create(['key' => 'test-role']);

    $user->addRole($role);

    expect($user->hasRole($role))->toBeTrue();

    $user->removeRole($role);

    expect($user->hasRole($role))->toBeFalse();
});

//  roles(): Collection
test('Deve recuperar a lista de permissões do model no storage', function () {
    $user = User::create(['email' => 'user@test.com']);

    $role = Role::create(['key' => 'test-role']);

    expect($user->roles()->count())->toBe(0);

    $user->addRole($role);

    expect($user->getRoles()->count())->toBe(1);
    expect($user->getRoles()->contains($role))->toBeTrue();
});

// roles(): Collection - CACHE
test('Ao pegar as permissões deve criar um cache com as permissões', function () {
    $user = User::create(['email' => 'user@test.com']);

    $key1 = 'roles::of::' . $user::class . '::' . $user->getKey();

    expect(Cache::has($key1))->toBeFalse();

    $user->getRoles();

    expect(Cache::has($key1))->toBeTrue();
});

// roles(): void
test('Deve esquecer o cache com as permissões do model', function () {
    $user = User::create(['email' => 'user@test.com']);

    $key1 = 'roles::of::' . $user::class . '::' . $user->getKey();

    expect(Cache::has($key1))->toBeFalse();

    $user->getRoles();

    expect(Cache::has($key1))->toBeTrue();

    $user->refreshRoles();

    expect(Cache::has($key1))->toBeFalse();
});


//  hasAnyRole(array $roles): bool
test('Deve retornar se o model tem uma dentre as permissões passadas', function () {
    $user = User::create(['email' => 'user@test.com']);

    $p1 = Role::create(['key' => 'test-role-1']);
    $p2 = Role::create(['key' => 'test-role-2']);
    $p3 = Role::create(['key' => 'test-role-3']);

    $user->addRole($p1);
    $user->addRole($p2);

    expect($user->hasAnyRole(['test-role-2']))->toBeTrue();
    expect($user->hasAnyRole(['test-role-3']))->toBeFalse();
});

// hasAllRoles(array $roles): bool
test('Deve retornar se o model tem todas as permissões passadas', function () {
    $user = User::create(['email' => 'user@test.com']);

    $r1 = Role::create(['key' => 'test-role-1']);
    $r2 = Role::create(['key' => 'test-role-2']);
    $r3 = Role::create(['key' => 'test-role-3']);

    $user->addRole($r1);
    $user->addRole($r2);

    expect($user->hasAllRoles(['test-role-2']))->toBeTrue();
    expect($user->hasAllRoles([$r1, $r2]))->toBeTrue();
    expect($user->hasAllRoles(['test-role-2', $r3]))->toBeFalse();
});

test('Ao excluir um model que tem hasRoles deve excluir o seu cache com roles', function () {
    $user = User::create(['email' => 'user@test.com']);
    $r = Role::create(['key' => 'role-test']);

    $user->addRole($r);

    $key = 'roles::of::' . $user::class . '::' . $user->getKey();

    expect(Cache::has($key))->toBeFalse();

    $user->getRoles();

    expect(Cache::has($key))->toBeTrue();

    $user->delete();

    expect(Cache::has($key))->toBeFalse();
});
