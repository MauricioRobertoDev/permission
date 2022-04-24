<?php

namespace MrDev\Permission\Observers;

use MrDev\Permission\Models\Permission;

class PermissionObserver
{
    public function updated(Permission $permission)
    {
        $permission->refreshStorage();
    }

    public function saved(Permission $permission)
    {
        $permission->refreshStorage();
    }

    public function deleted(Permission $permission)
    {
        $permission->refreshStorage();
    }
}
