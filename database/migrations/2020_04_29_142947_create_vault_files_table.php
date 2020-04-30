<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vault_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('file_name')->unique();
            $table->string('stored_file_name');
            $table->string('file_extension');
            $table->unsignedInteger('file_size');
            $table->unsignedInteger('file_size_v');
            $table->string('base64')->nullable();
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
        Schema::dropIfExists('vault_files');
    }
}
