<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FftTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ffts', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('user_id');
			$t->integer('job_id');
			$t->boolean('warranty');
			$t->text('notes');
			$t->boolean('closed');
			$t->datetime('schedule_start');
			$t->datetime('schedule_end');
			$t->datetime('pre_schedule_start');
			$t->datetime('pre_schedule_end');
			$t->integer('pre_assigned');
		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ffts');
	}

}
