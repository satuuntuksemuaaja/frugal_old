<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApplianceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('appliances', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
			$t->double('price', 10, 2);
			$t->integer('countas');
			$t->integer('designation_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('appliances');
	}

}
