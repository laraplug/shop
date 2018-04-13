<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopShopProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop__shop_product', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('shop_id')->unsigned();
            $table->integer('product_id')->unsigned();

            $table->index(['shop_id', 'product_id']);
            $table->unique(['shop_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop__shop_product');
    }
}
