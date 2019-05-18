<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JobsTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jobs', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('quote_id');
			$t->date('contract_date');
			$t->date('start_date');
			$t->boolean('closed');
			$t->datetime('closed_on');
			$t->text('meta'); // for received stuff and ordered.
			$t->boolean('paid');
			$t->boolean('locked');
			$t->text('notes');
		});

		Schema::create('job_schedules', function ($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->datetime('start');
			$t->datetime('end');
			$t->integer('designation_id');
			$t->integer('user_id');
			$t->integer('job_id');
			$t->boolean('complete');
			$t->boolean('sent');
			$t->text('notes');
		});

		Schema::create('job_items', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('job_id');
			$t->string('instanceof'); // What is this? Cabinet Hardware, what? Used to create the object of what we need.
			$t->string('reference'); // Used to verfiy against cabinet1, cabinet2, etc. could be a key in the quote meta.
			$t->date('ordered');
			$t->date('confirmed');
			$t->date('received');
			$t->date('verified');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('jobs');
		Schema::drop('job_schedules');
		Schema::drop('job_items');
	}

}
