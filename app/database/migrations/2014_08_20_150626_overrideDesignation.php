<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OverrideDesignation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('designations', function($t)
		{
			$t->string('override_email');
			$t->bigInteger('override_sms');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('designations', function($t)
		{
			$t->dropColumn('override_email');
			$t->dropColumn('override_sms');
		});
	}

}
