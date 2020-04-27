<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vault_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title')->unique();
            $table->string('text');
            $table->string('color')->default('#ffffff');
            $table->integer('font_size')->default(12);
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
        Schema::dropIfExists('vault_notes');
    }
}
