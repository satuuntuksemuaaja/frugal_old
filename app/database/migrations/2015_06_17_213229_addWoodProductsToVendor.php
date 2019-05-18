<?php

use Illuminate\Database\Migrations\Migration;

class AddWoodProductsToVendor extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendors', function($t)
		{
			$t->boolean('wood_products');
		});

		Schema::table('quote_cabinets', function($t)
		{
			$t->text('wood_xml');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendors', function($t)
		{
			$t->dropColumn('wood_products');
		});

		Schema::table('quote_cabinets', function($t)
		{
			$t->dropColumn('wood_xml');
		});
	}

}
