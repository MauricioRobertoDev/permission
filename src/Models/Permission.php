<?php

namespace MrDev\Permission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

        return static::query()->create($attributes);
    }

    public static function findById(string $id, string $guardName = null): Permission
    {
        $guardName = $guardName ?? GuardHelper::getGuardNameFor(static::class);

        $concretePermission = app('mr-permission')->getPermissions()->where('id', $id)->where('guard_name', $guardName)->first();

        if (! $concretePermission) {
            throw PermissionDoesNotExistException::withIdAndGuard($id, $guardName);
        }

        return $concretePermission;
    }

    public static function findByKey(string $key, string $guardName = null): Permission
    {
        $guardName = $guardName ?? GuardHelper::getGuardNameFor(static::class);

        $concretePermission = app('mr-permission')->getPermissions()->where('key', $key)->where('guard_name', $guardName)->first();

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
        return app('mr-permission')->getPermission($permission, $guardName ?? GuardHelper::getGuardNameFor(self::class));
    }
}
