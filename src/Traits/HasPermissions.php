<?php

namespace MrDev\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use MrDev\Permission\Expections\PermissionDoesNotExistException;
use MrDev\Permission\Helpers\GuardHelper;
use MrDev\Permission\Models\Permission;

trait HasPermissions
{
    public function permissions(): BelongsToMany
    {
        return $this->morphToMany(
            Permission::class,
            'model',
            'model_has_permissions',
            'model_id',
            'permission_id'
        );
    }

    public function addPermission(Permission|string|int $permission): void
    {
        $permission = Permission::getPermissionOrFail($permission, $guardName ?? $this->getGuardName());

        $this->permissions()->attach($permission);

        $this->refreshPermissions();
    }

    public function hasPermission(Permission|string|int $permission, $guardName = null): bool
    {
        $concretePermission = Permission::getPermission($permission, $guardName ?? $this->getGuardName());

        if (! $concretePermission) {
            throw PermissionDoesNotExistException::create($permission, $guardName ?? $this->getGuardName());
        }

        return $this->getPermissions()->contains($concretePermission);
    }

    public function removePermission(Permission|string|int $permission, $guardName = null): void
    {
        $permission = Permission::getPermission($permission, $guardName ?? $this->getGuardName());

        $this->permissions()->detach($permission);

        $this->refreshPermissions();
    }

    public function getPermissions(): Collection
    {
        return app('mr-permission')->getPermissionStorageOf($this);
    }

    public function refreshPermissions(): void
    {
        app('mr-permission')->refreshPermissionsOf($this);
    }

    protected function getGuardName(): string
    {
        return GuardHelper::getGuardNameFor(self::class);
    }
}
