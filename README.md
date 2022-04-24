# Mr. Permission

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrdev/permission.svg?style=flat-square)](https://packagist.org/packages/mrdev/permission)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/mrdev/permission/run-tests?label=tests)](https://github.com/mrdev/permission/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/mrdev/permission/Check%20&%20fix%20styling?label=code%20style)](https://github.com/mrdev/permission/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mrdev/permission.svg?style=flat-square)](https://packagist.org/packages/mrdev/permission)

Um simples pacote que adiciona permissões e cargos para um projeto laravel.
## Installation

Você pode instalar o pacote via composer:

```bash
composer require mrdev/permission
```

Publique e execute as migrations usando:

```bash
php artisan vendor:publish --tag="permission-migrations"
php artisan migrate
```

Adicionar a trait HasRoles no seu model de usuário:

```php
use MrDev\Permission\Traits\HasRoles;

class User extends Model {
    use HasRoles;
    ...
}
```

## Uso

#### Permissões

```php
use App\Models\User;
use MrDev\Permission\Models\Permission;


$permission = Permission::create(['key' => 'permissão']);

// ADICIONANDO

$user->addPermission($permission);
// OU
$user->addPermission('permissão');
// OU
$user->addPermission($permission->id);


// CHECANDO

$user->hasPermission('permissão');
// OU
$user->hasAnyPermission(['permissão', 'outra permissão']);
// OU
$user->hasAllPermissions(['permissão', 'outra permissão']);
```

#### Roles

```php
use App\Models\User;
use MrDev\Permission\Models\Role;

$role = Role::create(['key' => 'role']);

// ADICIONANDO

$user->addRole($role);
// OU
$user->addRole('role');
// OU
$user->addRole($role->id);


// CHECANDO

$user->hasRole('role');
// OU
$user->hasAnyRole(['role', 'outra role']);
// OU
$user->hasAllRoles(['role', 'outra role']);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Creditos

- [Mauricio Roberto](https://github.com/MauricioRobertoDev)
- [Todos os Contribuintes](../../contributors)

## Licença

Licença (MIT). Por favor veja [Licença](LICENSE.md) for more information.
