<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddfinancetotalToquote extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('quotes', function($t)
		{
			$t->double('finance_total', 10, 2); // for reports
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
			Schema::table('quotes', function($t)
		{
			$t->dropColumn('finance_total', 10, 2); // for reports
		});
	}

}
