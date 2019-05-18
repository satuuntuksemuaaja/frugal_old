<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UrgentTasks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tasks', function($t)
		{
			$t->boolean('urgent');
			$t->boolean('satisfied');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tasks', function($t)
		{
			$t->dropColumn('urgent');
			$t->dropColumn('satisfied');
		});
	}

}
