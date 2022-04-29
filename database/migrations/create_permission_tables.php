<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->string('key');
            $table->string('guard_name');
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->unique(['name', 'guard_name']);

            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignId('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            $table->string('key');
            $table->string('guard_name');
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->unique(['name', 'guard_name']);

            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignId('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');
    }
};
