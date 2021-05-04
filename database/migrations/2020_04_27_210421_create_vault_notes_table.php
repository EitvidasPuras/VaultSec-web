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
            $table->foreignId('user_id')->constrained();
            $table->string('title', 500)->default(null)->nullable();
            $table->string('text', 10000);
            $table->string('color')->default('#ffffff');
            $table->integer('font_size')->default(12);
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
        Schema::dropIfExists('vault_notes');
    }
}
