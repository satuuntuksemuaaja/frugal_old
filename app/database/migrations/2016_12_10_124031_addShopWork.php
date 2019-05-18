<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopWork extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shops', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->integer('user_id');
           $t->boolean('active')->default(1);
           $t->integer('job_id');
        });

		Schema::create('shop_cabinets', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->integer('quote_cabinet_id');
           $t->integer('shop_id');
           $t->text('notes');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shop_cabinets');
		Schema::drop('shops');
	}

}
