<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOrderStructs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('change_orders', function ($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('job_id');
			$t->integer('user_id');
			$t->text('signature');
			$t->datetime('signed_on');
			$t->boolean('signed');
			$t->boolean('billed');
			$t->boolean('closed');
			$t->boolean('sent');
			$t->datetime('sent_on');
		});

		Schema::create('change_order_details', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('change_order_id');
			$t->text('description');
			$t->double('price', 10, 2);
			$t->integer('user_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('change_orders');
		Schema::drop('change_order_details');
	}

}
