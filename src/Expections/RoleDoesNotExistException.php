<?php

namespace MrDev\Permission\Expections;

use Exception;
use MrDev\Permission\Models\Role;

final class RoleDoesNotExistException extends Exception
{
    public static function withIdAndGuard($id, $guardName): RoleDoesNotExistException
    {
        return new static("A role with id`{$id}` does not exists for guard `{$guardName}`.");
    }

    public static function withKeyAndGuard($key, $guardName): RoleDoesNotExistException
    {
        return new static("A role `{$key}` does not exists for guard `{$guardName}`.");
    }

    public static function create(Role|string|int $role, string $guardName): RoleDoesNotExistException
    {
        if (is_int($role)) {
            return static::withIdAndGuard($role, $guardName);
        }

        if (is_string($role)) {
            return static::withKeyAndGuard($role, $guardName);
        }

        return new static("A role does not exists.");
    }
}
