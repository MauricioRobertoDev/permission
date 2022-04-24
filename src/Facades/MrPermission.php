<?php

namespace MrDev\Permission\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MrDev\Permission\Permission
 */
class MrPermission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mr-permission';
    }
}
