<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopPaymentGatewayConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop__payment_gateway_configs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('shop_id')->unsigned();
            $table->string('gateway_id');

            $table->string('merchant_id')->nullable();
            $table->string('merchant_token')->nullable();
            $table->string('enabled_method_ids', 100);
            $table->string('options');

            $table->timestamps();

            $table->unique(['shop_id', 'gateway_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop__payment_gateway_configs');
    }
}
