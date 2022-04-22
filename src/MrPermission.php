<?php

namespace MrDev\Permission;

use Illuminate\Database\Eloquent\Collection;
use MrDev\Permission\Expections\PermissionDoesNotExistException;
use MrDev\Permission\Models\Permission;

class MrPermission
{
    public function getPermissions(): Collection
    {
        return Permission::all();
    }

    public function getPermission(Permission|string|int $permission, string $guardName): Permission
    {
        if (is_string($permission)) {
            return $permission = Permission::findByKey($permission, $guardName);
        }

        if (is_int($permission)) {
            return $permission = Permission::findById($permission, $guardName);
        }

        if (! $this->getPermissions()->contains($permission)) {
            throw new PermissionDoesNotExistException('Permission not found');
        }

        return $permission;
    }
}
