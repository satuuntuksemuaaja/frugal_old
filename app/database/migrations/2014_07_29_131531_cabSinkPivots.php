<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CabSinkPivots extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('quote_cabinets', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('quote_id');
			$t->text('data');
			$t->text('override');
			$t->string('location');
			$t->boolean('measure');
			$t->string('color');
			$t->integer('cabinet_id');
			$t->string('name');
			$t->string('inches')->default(0);
			$t->double('price', 10, 2);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('quote_cabinets');
	}

}
