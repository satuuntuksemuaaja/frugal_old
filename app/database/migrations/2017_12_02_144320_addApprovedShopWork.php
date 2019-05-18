<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovedShopWork extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('shop_cabinets', function($t)
        {
           $t->timestamp('approved')->nullable();
           $t->timestamp('started')->nullable();
           $t->timestamp('completed')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('shop_cabinets', function($t)
        {
            $t->dropColumn('approved');
            $t->dropColumn('started');
            $t->dropColumn('completed');
        });
	}

}
