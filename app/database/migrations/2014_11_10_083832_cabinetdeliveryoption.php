<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Cabinetdeliveryoption extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('quote_cabinets', function($t)
		{
			$t->string('delivery');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('quote_cabinets', function($t)
		{
			$t->dropColumn('delivery');
		});
	}

}
