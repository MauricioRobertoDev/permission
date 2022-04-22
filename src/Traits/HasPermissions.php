<?php

namespace MrDev\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    }

    public function hasPermission(Permission|string|int $permission, $guardName = null): bool
    {
        $permission = Permission::getPermission($permission, $guardName ?? $this->getGuardName());

        if (! $permission) {
            return false;
        }

        return $this->permissions()->get()->contains($permission);
    }

    public function removePermission(Permission|string|int $permission, $guardName = null): void
    {
        $permission = Permission::getPermission($permission, $guardName ?? $this->getGuardName());

        $this->permissions()->detach($permission);
    }

    protected function getGuardName(): string
    {
        return GuardHelper::getGuardNameFor(self::class);
    }
}
