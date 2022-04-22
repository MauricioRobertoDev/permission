<?php

namespace MrDev\Permission\Observers;

use MrDev\Permission\Models\Permission;

class PermissionObserver
{
    public function updated(Permission $permission)
    {
        $permission->refreshPermissions();
    }

    public function saved(Permission $permission)
    {
        $permission->refreshPermissions();
    }

    public function deleted(Permission $permission)
    {
        $permission->refreshPermissions();
    }
}
