<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QuestionaireCats extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('questions', function($t)
		{
			$t->integer('question_category_id');
		});

		Schema::create('question_categories', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('questions', function($t)
		{
			$t->dropColumn('question_category_id');
		});
	}

}
