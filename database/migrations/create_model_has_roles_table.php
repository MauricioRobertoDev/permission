<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignId('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_roles');
    }
};
