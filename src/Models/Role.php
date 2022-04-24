<?php

namespace MrDev\Permission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use MrDev\Permission\Expections\GuardDoesNotExists;
use MrDev\Permission\Expections\RoleAlreadyExists;
use MrDev\Permission\Expections\RoleDoesNotExistException;
use MrDev\Permission\Helpers\GuardHelper;
use MrDev\Permission\Traits\HasPermissions;

class Role extends Model
{
    use HasFactory;
    use HasPermissions;

    protected $fillable = [
        'key',
        'name',
        'guard_name',
        'description',
    ];

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

    public static function create(array $attributes = []): Role
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? GuardHelper::getGuardNameFor(static::class);

        if (! GuardHelper::guardExists($attributes['guard_name'])) {
            throw GuardDoesNotExists::guard($attributes['guard_name']);
        }

        $role = self::getRole($attributes['key'], $attributes['guard_name']);

        if ($role) {
            throw RoleAlreadyExists::withKeyAndGuard($attributes['key'], $attributes['guard_name']);
        }

        $role = self::query()->create($attributes);

        return $role;
    }

    public static function getAllRoles(): Collection
    {
        return self::storage();
    }

    public static function getRole(Role|string|int $role, string $guardName = null): Role|null
    {
        try {
            return self::getRoleOrFail($role, $guardName ?? GuardHelper::getGuardNameFor(self::class));
        } catch (RoleDoesNotExistException $th) {
            //throw $th;
            return null;
        }
    }

    public static function getRoleOrFail(Role|string|int $role, string $guardName = null): Role
    {
        if (is_string($role)) {
            return self::findByKey($role, $guardName);
        }

        if (is_int($role)) {
            return self::findById($role, $guardName);
        }

        if (! self::getAllroles()->contains($role)) {
            throw RoleDoesNotExistException::create($role, $guardName);
        }

        $role = self::findByKey($role->key, $role->guard_name);

        return $role;
    }

    public static function findById(string $id, string $guardName = null): Role
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        /** @var Role $concreteRole */
        $concreteRole = self::storage()->where('id', $id)->where('guard_name', $guardName)->first();

        if (! $concreteRole) {
            throw RoleDoesNotExistException::withIdAndGuard($id, $guardName);
        }

        return $concreteRole;
    }

    public static function findByKey(string $key, string $guardName = null): Role
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        /** @var Role $concreteRole */
        $concreteRole = self::storage()->where('key', $key)->where('guard_name', $guardName)->first();

        if (! $concreteRole) {
            throw RoleDoesNotExistException::withKeyAndGuard($key, $guardName);
        }

        return $concreteRole;
    }

    public static function exists(string $key, string $guardName = null): bool
    {
        return self::getRole($key, $guardName) !== null;
    }

    public static function refreshStorage(): void
    {
        app('mr-permission')->refreshRoleStorage();
    }

    protected static function storage(): Collection
    {
        return app('mr-permission')->getRoleStorage();
    }
}
