<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuoteTiles extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('quote_tiles', function($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('quote_id');
            $t->string('description');
            $t->double('linear_feet_counter');
            $t->double('backsplash_height');
            $t->string('pattern');
            $t->string('sealed');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('quote_tiles');
	}

}
