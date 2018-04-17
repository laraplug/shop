<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop__shops', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('subdomain')->default('');

            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->string('company_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('email')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address')->nullable();
            $table->string('address_detail')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->decimal('lat', 10, 8)->default(0);
            $table->decimal('lng', 11, 8)->default(0);
            $table->string('currency_code')->nullable();
            $table->string('theme')->nullable();

            $table->timestamps();

            $table->unique('subdomain');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop__shops');
    }
}
