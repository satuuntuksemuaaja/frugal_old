<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FftContractSign extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ffts', function($t)
		{
			$t->datetime('signed');
			$t->text('signature');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ffts', function($t)
		{
			$t->dropColumn('signed');
			$t->dropColumn('signature');
		});
	}

}
