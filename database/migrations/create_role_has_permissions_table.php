<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MrDev\Permission\Models\Permission;
use MrDev\Permission\Models\Role;

return new class () extends Migration {
    public function up()
    {
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignIdFor(Role::class);
            $table->foreignIdFor(Permission::class)->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_has_permissions');
    }
};
