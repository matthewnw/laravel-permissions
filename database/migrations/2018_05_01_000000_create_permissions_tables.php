<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permissions.table_names');

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('identity')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(0);
            $table->timestamps();
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('identity')->unique();
            $table->text('description')->nullable();
            $table->boolean('default')->default(0);
            $table->boolean('active')->default(0);
            $table->integer('level')->default(99);
            $table->timestamps();
        });

        Schema::create($tableNames['role_permissions'], function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('permission_id');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create($tableNames['user_roles'], function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('user_id');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['role_id', 'user_id']);
        });

        Schema::create($tableNames['user_permissions'], function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('permission_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->unique(['user_id', 'permission_id']);
        });

        app('cache')->forget('matthewnw.permissions.cache');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permissions.table_names');

        Schema::dropIfExists($tableNames['user_permissions']);
        Schema::dropIfExists($tableNames['user_roles']);
        Schema::dropIfExists($tableNames['role_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
}
