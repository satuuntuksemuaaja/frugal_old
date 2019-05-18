<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchedulesSent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('jobs', function($t)
		{
			$t->boolean('schedules_sent');
			$t->boolean('schedules_confirmed');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('jobs', function($t)
		{
			$t->dropColumn('schedules_sent');
			$t->dropColumn('schedules_confirmed');
		});
	}

}
