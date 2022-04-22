<?php

namespace MrDev\Permission\Traits;

trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache()
    {
        static::saved(function () {
            dump('saved');
        });

        static::deleted(function () {
            dump('deleted');
        });
    }
}
