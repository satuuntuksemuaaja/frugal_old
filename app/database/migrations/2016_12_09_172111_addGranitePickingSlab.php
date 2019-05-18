<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGranitePickingSlab extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('quotes', function($t)
        {
           $t->string('picking_slab');
           $t->boolean('picked_slab');
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
            $t->dropColumn('picking_slab');
            $t->dropColumn('picked_slab');
        });

    }

}
