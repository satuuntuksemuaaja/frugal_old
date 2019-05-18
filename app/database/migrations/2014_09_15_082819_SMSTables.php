<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SMSTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('smses', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('source');
			$t->string('destination');
			$t->integer('user_id');
			$t->string('message');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('smses');
	}

}
