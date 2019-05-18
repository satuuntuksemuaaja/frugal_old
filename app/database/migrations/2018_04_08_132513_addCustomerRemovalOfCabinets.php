<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerRemovalOfCabinets extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('quote_cabinets', function($t)
        {
           $t->boolean('customer_removed')->default(0);
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
            $t->dropColumn('customer_removed');
        });
	}

}
