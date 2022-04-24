<?php

namespace MrDev\Permission\Expections;

use Exception;

final class StorageKeyNotFound extends Exception
{
    protected string $message = "Storage key not found";
}
