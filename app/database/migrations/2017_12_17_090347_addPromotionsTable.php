<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPromotionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('promotions', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->string('name');
           $t->boolean('active')->default(0);
           $t->string('modifier'); // GRANITE_SQFT, etc.
            $t->string('condition'); // < > =
            $t->double('qualifier');
            $t->double('discount_amount'); // based on the value sent into the method
            $t->string('verbiage', 1024);
        });

		Schema::table('quotes', function($t)
        {
           $t->integer('promotion_id')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('quotes', function($t)
        {
           $t->dropColumn('promotion_id');
        });

		Schema::drop('promotions');
	}

}
