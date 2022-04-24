<?php

namespace MrDev\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use MrDev\Permission\Expections\GuardDoesNotExists;
use MrDev\Permission\Expections\GuardDoesNotMatch;
use MrDev\Permission\Expections\RoleDoesNotExistException;
use MrDev\Permission\Helpers\GuardHelper;
use MrDev\Permission\Models\Role;

trait HasRoles
{
    use HasPermissions;

    public function roles(): BelongsToMany
    {
        return $this->morphToMany(
            Role::class,
            'model',
            'model_has_roles',
            'model_id',
            'role_id'
        );
    }

    public function addRole(Role|string|int $role, string $guardName = null): void
    {
        $guardName = $guardName ?? GuardHelper::getGuardNameFor(self::class);

        $concreteRole = Role::getRoleOrFail($role, $guardName);

        $this->ensureModelSharesGuard($concreteRole);

        $this->roles()->attach($concreteRole);

        $this->refreshRoles();
    }

    public function hasRole(Role|string|int $role, $guardName = null): bool
    {
        $guardName = $guardName ?? GuardHelper::getGuardNameFor(self::class);

        $concreteRole = Role::getRole($role, $guardName);

        if (! $concreteRole) {
            throw RoleDoesNotExistException::create($role, $guardName);
        }

        return $this->getRoles()->contains($concreteRole);
    }

    public function removeRole(Role|string|int $role, $guardName = null): void
    {
        $role = Role::getRole($role, $guardName ?? GuardHelper::getGuardNameFor(self::class));

        $this->roles()->detach($role);

        $this->refreshRoles();
    }

    protected function ensureModelSharesGuard($groupOrPermission): void
    {
        if (! GuardHelper::guardExists($groupOrPermission->guard_name)) {
            throw GuardDoesNotExists::guardOfPermissionOrRole($groupOrPermission);
        }

        if (! GuardHelper::getPossibleGuards($this)->contains($groupOrPermission->guard_name)) {
            throw GuardDoesNotMatch::create($groupOrPermission->guard_name, GuardHelper::getPossibleGuards($this));
        }
    }

    public function refreshRoles(): void
    {
        app('mr-permission')->refreshRoleStorageOf($this);
    }

    public function getRoles(): Collection
    {
        return app('mr-permission')->getRoleStorageOf($this);
    }
}
