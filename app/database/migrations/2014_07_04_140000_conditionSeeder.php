<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConditionSeeder extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('conditions', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('question_id');
			$t->string('answer');
			$t->enum('operand', ['Add', 'Subtract']);
			$t->double('amount', 10, 2);
			$t->boolean('once');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('conditions');

	}

}
