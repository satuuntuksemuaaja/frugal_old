<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePOCompStruct extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('pos', function($t)
		{
			$t->string('company_invoice');
			$t->string('projected_ship');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('pos', function($t)
		{
			$t->dropColumn('company_invoice');
			$t->dropColumn('projected_ship');
		});
	}

}
