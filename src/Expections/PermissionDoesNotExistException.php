<?php

namespace MrDev\Permission\Expections;

use Exception;

class PermissionDoesNotExistException extends Exception
{
    public static function withIdAndGuard($id, $guardName)
    {
        return new static("A permission with id`{$id}` does not exists for guard `{$guardName}`.");
    }

    public static function withKeyAndGuard($key, $guardName)
    {
        return new static("A permission `{$key}` does not exists for guard `{$guardName}`.");
    }
}
