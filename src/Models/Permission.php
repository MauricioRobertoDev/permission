<?php

namespace MrDev\Permission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use MrDev\Permission\Expections\PermissionAlreadyExists;
use MrDev\Permission\Expections\PermissionDoesNotExistException;
use MrDev\Permission\Helpers\GuardHelper;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'guard_name',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            GuardHelper::getModelForGuard($this->attributes['guard_name']),
            'model',
            'model_has_permissions',
            'permission_id',
            'model_id',
        );
    }

    public static function create(array $attributes = []): Permission
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? GuardHelper::getGuardNameFor(static::class);

        $permission = static::getPermission($attributes['key'], $attributes['guard_name']);

        if ($permission) {
            throw PermissionAlreadyExists::withKeyAndGuard($attributes['key'], $attributes['guard_name']);
        }

        $permission = self::query()->create($attributes);

        return $permission;
    }

    public static function findById(string $id, string $guardName = null): Permission
    {
        $guardName = $guardName ?? GuardHelper::getGuardNameFor(static::class);

        $concretePermission = app('mr-permission')->getPermissionStorage()->where('id', $id)->where('guard_name', $guardName)->first();

        if (! $concretePermission) {
            throw PermissionDoesNotExistException::withIdAndGuard($id, $guardName);
        }

        return $concretePermission;
    }

    public static function findByKey(string $key, string $guardName = null): Permission
    {
        $guardName = $guardName ?? GuardHelper::getGuardNameFor(static::class);

        $concretePermission = app('mr-permission')->getPermissionStorage()->where('key', $key)->where('guard_name', $guardName)->first();

        // dump(app('mr-permission')->getPermissionStorage());

        if (! $concretePermission) {
            throw PermissionDoesNotExistException::withKeyAndGuard($key, $guardName);
        }

        return $concretePermission;
    }

    public static function getPermission(Permission|string|int $permission, string $guardName = null): Permission|null
    {
        try {
            return self::getPermissionOrFail($permission, $guardName ?? GuardHelper::getGuardNameFor(self::class));
        } catch (PermissionDoesNotExistException $th) {
            //throw $th;
            return null;
        }
    }

    public static function getPermissionOrFail(Permission|string|int $permission, string $guardName = null): Permission
    {
        if (is_string($permission)) {
            return $permission = self::findByKey($permission, $guardName);
        }

        if (is_int($permission)) {
            return $permission = self::findById($permission, $guardName);
        }

        if (! self::getAllPermissions()->contains($permission)) {
            throw PermissionDoesNotExistException::create($permission, $guardName);
        }

        return $permission;
    }

    public static function getAllPermissions(): Collection
    {
        return app('mr-permission')->getPermissionStorage();
    }

    public static function refreshPermissions(): void
    {
        app('mr-permission')->refreshPermissions();
    }

    public static function exists(string $key, string $guardName = null): bool
    {
        return self::getPermission($key, $guardName) !== null;
    }
}
