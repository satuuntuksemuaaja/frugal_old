<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Mobilepunches extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('punches', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('designation_id');
			$t->string('question');
		});

		Schema::create('punch_answers', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('job_id');
			$t->integer('user_id');
			$t->integer('punch_id');
			$t->string('answer');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('punches');
		Schema::drop('punch_answers');
	}

}
