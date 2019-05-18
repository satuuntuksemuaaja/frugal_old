<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SnapshotStorage extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('snapshots', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('quote_id');
			$t->text('quote');
			$t->text('debug');
			$t->string('location');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('snapshots');
	}

}
