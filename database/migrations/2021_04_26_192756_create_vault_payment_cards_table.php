<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultPaymentCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vault_payment_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('title', 1000)->nullable()->default(null);
            $table->string('card_number', 500);
            $table->string('expiration_mm', 400);
            $table->string('expiration_yy', 400);
            $table->string('type', 100);
            $table->string('cvv', 400);
            $table->string('pin', 400);
            $table->string('ip_address');
            $table->boolean('currently_shared')->default(false);
            $table->timestamp('created_at_device')->default(now());
            $table->timestamp('updated_at_device')->default(now());
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vault_payment_cards');
    }
}
