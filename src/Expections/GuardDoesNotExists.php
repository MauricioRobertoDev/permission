<?php

namespace MrDev\Permission\Expections;

use Exception;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Models\Role;

final class GuardDoesNotExists extends Exception
{
    public static function guardOfPermissionOrRole(Permission|Role $permissionOrRole): self
    {
        return new static("The guard `{$permissionOrRole->guard_name}` of `{$permissionOrRole->key}` does not exists.");
    }

    public static function guard(string $guardName): self
    {
        return new static("The guard `{$guardName}` does not exists.");
    }
}
