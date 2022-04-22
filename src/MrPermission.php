<?php

namespace MrDev\Permission;

// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Traits\HasPermissions;

class MrPermission
{
    public function getPermissionStorage(): Collection
    {
        $key = 'permissions::all';

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $permissions = Permission::all();

        Cache::forever($key, $permissions);

        return $permissions;
    }

    public function refreshPermissions()
    {
        $key = 'permissions::all';

        Cache::forget($key);
    }

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

    public function refreshPermissionsOf(Model $model): void
    {
        $key = 'permissions::of::' . $model::class . '::' . $model->getKey();

        Cache::forget($key);
    }
}