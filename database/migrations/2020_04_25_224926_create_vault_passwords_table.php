<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultPasswordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vault_passwords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title', 500)->default(null)->nullable();
            $table->string('url', 500)->default(null)->nullable();
            $table->string('login', 3000)->default(null)->nullable();
            $table->string('password', 10000);
            $table->string('category')->default('Unassigned');
            $table->string('color')->default('#ffffff');
            $table->string('ip_address');
            $table->boolean('currently_shared')->default(false);
            $table->timestamp('created_at_device')->default(now());
            $table->timestamp('updated_at_device')->default(now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vault_passwords');
    }
}
