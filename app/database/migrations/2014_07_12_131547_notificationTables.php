<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NotificationTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notifications', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('isFor'); // Lead Status, Job, FFT, Warranty?
			$t->integer('reference'); // What id? Used to check to see if status is still the same.
			$t->integer('status_id'); // Status that this notification is for.
			$t->integer('expiration_id'); // What list of actions need to be done?
			$t->datetime('set');	//Set on
			$t->datetime('expires'); // When does this notification fire?
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notifications');
	}

}
