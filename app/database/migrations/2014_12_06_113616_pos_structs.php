<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PosStructs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pos', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('number');
			$t->integer('customer_id'); // - 0 is internal
			$t->string('title');
			$t->integer('user_id');
			$t->string('status');
			$t->datetime('submitted');
			$t->datetime('committed');
			$t->boolean('archived');
			$t->integer('vendor_id');
			$t->string('type'); // Cabinet, Hardware, Accessories, Other
			$t->integer('job_id'); // If part of a job.
		});

		Schema::create('po_items', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('po_id');
			$t->integer('job_item_id'); // If directly linked.
			$t->string('item');
			$t->datetime('received');
			$t->integer('received_by');
			$t->integer('user_id');
			$t->text('notes');
			$t->integer('qty');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pos');
		Schema::drop('po_items');
	}

}
