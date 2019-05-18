<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPunchtoPo extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('po_items', function($t)
        {
           $t->boolean('punch');
        });

		Schema::table('job_items', function($t)
        {
           $t->integer('po_item_id');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('po_items', function($t)
        {
            $t->dropColumn('punch');
        });

        Schema::table('job_items', function($t)
        {
            $t->dropColumn('po_item_id');
        });
	}

}
