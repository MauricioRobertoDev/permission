<?php

use MrDev\Permission\Helpers\GuardHelper;
use MrDev\Permission\Tests\Admin;
use MrDev\Permission\Tests\User;

// getPossibleGuards(Model|string $model): Collection
test('Deve retornar em uma collection o nome de todos os guards possíveis para determinado model', function () {
    expect(GuardHelper::getPossibleGuards(User::class))->toEqual(collect(['web', 'default', 'api']));
    expect(GuardHelper::getPossibleGuards(Admin::class))->toEqual(collect(['admin']));
});

// getGuardNameFor(Model|string $model): string
test('Deve retornar o nome do guard de determinado model', function () {
    expect(GuardHelper::getGuardNameFor(User::class))->toEqual('default');
    expect(GuardHelper::getGuardNameFor(Admin::class))->toEqual('admin');
});

// getModelForGuard(string $guard): string
test('Deve retornar a classe do model para determinado guard', function () {
    expect(GuardHelper::getModelForGuard('default'))->toEqual(User::class);
    expect(GuardHelper::getModelForGuard('admin'))->toEqual(Admin::class);
});
