<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CustomerTasks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tasks', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('user_id');
			$t->integer('assigned_id');
			$t->string('subject');
			$t->text('body');
			$t->integer('job_id');
			$t->integer('customer_id');
			$t->boolean('closed');
			$t->date('due');
		});

		Schema::create('task_notes', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('task_id');
			$t->integer('user_id');
			$t->text('body');
		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tasks');
		Schema::drop('task_notes');
	}

}
