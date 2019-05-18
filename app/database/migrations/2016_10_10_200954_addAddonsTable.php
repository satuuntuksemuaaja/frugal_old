<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddonsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::create('addons', function($t)
        {
           $t->increments('id');
            $t->timestamps();
            $t->string('item');
            $t->double('price');
            $t->boolean('active');
        });

        Schema::create('quote_addons', function($t)
        {
           $t->increments('id');
            $t->timestamps();
            $t->integer('quote_id');
            $t->integer('addon_id');
            $t->double('price');
            $t->double('qty');
        });

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('quote_addons');
        Schema::drop('addons');
	}

}
