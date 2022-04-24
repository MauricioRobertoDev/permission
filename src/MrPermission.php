<?php

namespace MrDev\Permission;

// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Models\Role;
use MrDev\Permission\Traits\HasPermissions;
use MrDev\Permission\Traits\HasRoles;

class MrPermission
{
    // PERMISSIONS
    public function getPermissionStorage(): Collection
    {
        $key = 'mrdev::cache::permissions::all';

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $permissions = Permission::all();

        Cache::forever($key, $permissions);

        return $permissions;
    }

    public function refreshPermissionStorage()
    {
        $key = 'mrdev::cache::permissions::all';

        Cache::forget($key);
    }

    // USER - PERMISSIONS
    public function getPermissionStorageOf(Model $model): Collection
    {
        $key = 'permissions::of::' . $model::class . '::' . $model->getKey();

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        if (in_array(HasPermissions::class, class_uses_recursive($model))) {
            $permissions = $model->permissions()->get();

            Cache::forever($key, $permissions);

            return $permissions;
        }

        return collect([]);
    }

    public function refreshPermissionStorageOf(Model $model): void
    {
        $key = 'permissions::of::' . $model::class . '::' . $model->getKey();

        Cache::forget($key);
    }

    // ROLE
    public function getRoleStorage(): Collection
    {
        $key = 'mrdev::cache::roles::all';

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $roles = Role::all();

        Cache::forever($key, $roles);

        return $roles;
    }

    public function refreshRoleStorage(): void
    {
        $key = 'mrdev::cache::roles::all';

        Cache::forget($key);
    }

    // USER - ROLES
    public function getRoleStorageOf(Model $model): Collection
    {
        $key = 'roles::of::' . $model::class . '::' . $model->getKey();

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        if (in_array(HasRoles::class, class_uses_recursive($model))) {
            $roles = $model->roles()->get();

            Cache::forever($key, $roles);

            return $roles;
        }

        return collect([]);
    }

    public function refreshRoleStorageOf(Model $model): void
    {
        $key = 'roles::of::' . $model::class . '::' . $model->getKey();

        Cache::forget($key);
    }
}
