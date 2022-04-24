<?php

namespace MrDev\Permission\Expections;

use Exception;

class PermissionAlreadyExists extends Exception
{
    public static function withKeyAndGuard($key, $guardName)
    {
        return new static("Permission `{$key}` already exists for guard `{$guardName}`.");
    }
}
