<?php

namespace MrDev\Permission\Expections;

use Exception;

final class RoleAlreadyExists extends Exception
{
    public static function withKeyAndGuard($key, $guardName)
    {
        return new static("Role `{$key}` already exists for guard `{$guardName}`.");
    }
}
