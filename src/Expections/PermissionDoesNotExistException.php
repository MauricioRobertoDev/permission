<?php

namespace MrDev\Permission\Expections;

use Exception;
use MrDev\Permission\Models\Permission;

final class PermissionDoesNotExistException extends Exception
{
    public static function withIdAndGuard($id, $guardName): PermissionDoesNotExistException
    {
        return new static("A permission with id`{$id}` does not exists for guard `{$guardName}`.");
    }

    public static function withKeyAndGuard($key, $guardName): PermissionDoesNotExistException
    {
        return new static("A permission `{$key}` does not exists for guard `{$guardName}`.");
    }

    public static function create(Permission|string|int $permission, string $guardName): PermissionDoesNotExistException
    {
        if (is_int($permission)) {
            return static::withIdAndGuard($permission, $guardName);
        }

        if (is_string($permission)) {
            return static::withKeyAndGuard($permission, $guardName);
        }

        return new static("A permission does not exists.");
    }
}
