<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerAuthorizations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('authorizations', function($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('job_id');
            $t->text('signature');
            $t->timestamp('signed_on');
        });

        Schema::create('authorization_items', function($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('authorization_id');
            $t->string('item');
        });

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('authorization_items');
        Schema::drop('authorizations');
	}

}
