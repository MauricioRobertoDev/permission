<?php

namespace MrDev\Permission\Contracts;

use MrDev\Permission\Models\Role;

interface RoleContract
{
    public static function getRole(Role|string|int $role, string $guardName = null): Role|null;

    public static function getRoleOrFail(Role|string|int $role, string $guardName = null): Role;

    public static function findById(string $id, string $guardName = null): Role;

    public static function findByKey(string $key, string $guardName = null): Role;
}
