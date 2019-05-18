<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShowroomClosingDigital extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('showrooms', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('lead_id');
			$t->datetime('scheduled');
			$t->string('location');
		});


		Schema::create('closings', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('lead_id');
			$t->datetime('scheduled');
			$t->string('location');
		});

		Schema::create('measures', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('lead_id');
			$t->datetime('scheduled');
			$t->string('location');
			$t->integer('measurer_id');
		});

	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('showrooms');
		Schema::drop('closings');
		Schema::drop('measures');
	}

}
