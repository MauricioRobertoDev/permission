<?php

namespace MrDev\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use MrDev\Permission\Expections\GuardDoesNotExists;
use MrDev\Permission\Expections\GuardDoesNotMatch;
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

    public static function bootHasPermissions(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->permissions()->detach();
            $model->refreshPermissions();
        });
    }

    public function addPermission(Permission|string|int $permission, string $guardName = null): void
    {
        $guardName = $guardName ?? GuardHelper::getGuardNameFor($this);

        $concretePermission = Permission::getPermissionOrFail($permission, $guardName);

        $this->ensureModelSharesGuard($concretePermission);

        $this->permissions()->attach($concretePermission);

        $this->refreshPermissions();
    }

    public function hasPermission(Permission|string|int $permission, $guardName = null): bool
    {
        $guardName = $guardName ?? GuardHelper::getGuardNameFor($this);

        $concretePermission = Permission::getPermissionOrFail($permission, $guardName);

        return $this->getPermissions()->contains($concretePermission) || $this->hasPermissionThroughRole($concretePermission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function hasPermissionThroughRole(Permission|string|int $permission, $guardName = null): bool
    {
        if (! method_exists($this, 'getRoles')) {
            return false;
        }

        $guardName = $guardName ?? GuardHelper::getGuardNameFor($this);

        $concretePermission = Permission::getPermissionOrFail($permission, $guardName);

        foreach ($this->getRoles() as $role) {
            if ($role->hasPermission($concretePermission)) {
                return true;
            }
        }

        return false;
    }

    public function removePermission(Permission|string|int $permission, $guardName = null): void
    {
        $permission = Permission::getPermission($permission, $guardName ?? GuardHelper::getGuardNameFor(self::class));

        $this->permissions()->detach($permission);

        $this->refreshPermissions();
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

    public function refreshPermissions(): void
    {
        app('mr-permission')->refreshPermissionStorageOf($this);
    }

    public function getPermissions(): Collection
    {
        return app('mr-permission')->getPermissionStorageOf($this);
    }

    public function listPermissions(): array
    {
        return $this->getPermissions()->pluck('key')->toArray();
    }
}
