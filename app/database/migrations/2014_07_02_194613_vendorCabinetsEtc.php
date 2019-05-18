<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VendorCabinetsEtc extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendors', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
			$t->integer('tts');
			$t->double('multiplier', 10,4);
			$t->double('freight', 10,2);
			$t->double('buildup', 10,2);
			$t->text('colors');
		});

		Schema::create('cabinets', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('frugal_name');
			$t->string('name');
			$t->double('price', 10, 2);
			$t->integer('vendor_id');
		});

		Schema::create('granites', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
			$t->double('price', 10, 2);
			$t->double('removal_price', 10, 2);
		});

		Schema::create('accessories', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('sku');
			$t->text('description');
			$t->string('name');
			$t->double('price');
			$t->integer('vendor_id');
			$t->boolean('on_site');
		});

		Schema::create('hardwares', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('sku');
			$t->text('description');
			$t->integer('vendor_id');
			$t->double('price', 10, 2);
		});

		Schema::create('sinks', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
			$t->double('price', 10, 2);
			$t->string('material');
		});

		Schema::create('extras', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
			$t->double('price', 10, 2);
		});

		Schema::create('questions', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('question');
			$t->string('response_type');
			$t->string('stage');
			$t->integer('designation_id');
			$t->boolean('contract');
			$t->string('contract_format');
		});

		Schema::create('quote_questions', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('question_id');
			$t->integer('quote_id');
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
		Schema::drop('vendors');
		Schema::drop('cabinets');
		Schema::drop('granites');
		Schema::drop('accessories');
		Schema::drop('hardwares');
		Schema::drop('sinks');
		Schema::drop('extras');
		Schema::drop('questions');
		Schema::drop('quote_questions');
	}

}
