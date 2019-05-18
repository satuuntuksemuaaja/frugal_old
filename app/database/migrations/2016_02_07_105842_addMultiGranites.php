<?php

use Illuminate\Database\Migrations\Migration;

class AddMultiGranites extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_granites', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('quote_id');
            $t->string('description');
            $t->integer('granite_id');
            $t->string('granite_override');
            $t->double('pp_sqft');
			$t->string('removal_type');
            $t->string('measurements');
            $t->string('counter_edge');
            $t->double('counter_edge_ft');
            $t->double('backsplash_height');
            $t->double('raised_bar_length');
            $t->double('raised_bar_depth');
            $t->double('island_width');
            $t->double('island_length');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('quote_granites');
    }

}
