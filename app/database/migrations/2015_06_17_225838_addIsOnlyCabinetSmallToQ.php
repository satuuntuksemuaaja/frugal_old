<?php

use Illuminate\Database\Migrations\Migration;

class AddIsOnlyCabinetSmallToQ extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('questions', function($t)
		{
			$t->boolean('small_job');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('questions', function($t)
		{
			$t->dropColumn('small_job');
		}); //
	}

}
