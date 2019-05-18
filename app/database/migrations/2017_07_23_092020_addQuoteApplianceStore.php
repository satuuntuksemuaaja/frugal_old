<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuoteApplianceStore extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('quote_appliances', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->integer('quote_id');
           $t->integer('appliance_id');
           $t->string('brand');
           $t->string('model');
           $t->string('size');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('quote_appliances');
	}

}
