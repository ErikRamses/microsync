<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hostname')->unique();
            $table->string('apikey')->nullable();
            $table->string('token')->nullable();
            $table->string('nonce')->nullable();
            $table->string('secret_key')->nullable();
            $table->string('email')->nullable();
            $table->string('patform')->default('shopify');
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
        Schema::dropIfExists('shops');
    }
}
