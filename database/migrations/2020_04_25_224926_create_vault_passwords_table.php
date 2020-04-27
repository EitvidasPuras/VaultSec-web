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
            $table->string('title')->unique();
            $table->string('website_name');
            $table->string('login');
            $table->string('password');
            $table->string('category')->default('Unassigned');
            $table->string('ip_address');
            $table->boolean('currently_shared')->default(false);
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
