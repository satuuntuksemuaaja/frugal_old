<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NotesConverstion extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		foreach (Customer::all() as $customer)
		{
			if ($customer->notes)
			{
				$note = new Note;
				$note->note = $customer->notes;
				$note->user_id = 5;
				$note->customer_id = $customer->id;
				$note->save();
			}
		}
		Schema::table('customers', function($t)
		{
			$t->dropColumn('notes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
