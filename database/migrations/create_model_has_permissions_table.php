<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MrDev\Permission\Models\Permission;

return new class () extends Migration {
    public function up()
    {
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignIdFor(Permission::class)->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_permissions');
    }
};
