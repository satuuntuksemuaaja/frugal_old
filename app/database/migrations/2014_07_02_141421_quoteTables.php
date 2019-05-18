<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QuoteTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('quotes', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->boolean('accepted');
			$t->boolean('final');
			$t->text('meta');
			$t->string('type');
			$t->integer('lead_id');
			$t->boolean('closed');
			$t->boolean('suspended');
			$t->double('price', 10, 2);
			$t->string('title');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('quotes');
	}

}
