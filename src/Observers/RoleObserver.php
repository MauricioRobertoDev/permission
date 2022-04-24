<?php

namespace MrDev\Permission\Observers;

use MrDev\Permission\Models\Role;

class RoleObserver
{
    public function updated(Role $role)
    {
        $role->refreshStorage();
    }

    public function saved(Role $role)
    {
        $role->refreshStorage();
    }

    public function deleted(Role $role)
    {
        $role->refreshStorage();
    }
}
