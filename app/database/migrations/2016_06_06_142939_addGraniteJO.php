<?php

use Illuminate\Database\Migrations\Migration;

class AddGraniteJO extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_granites', function ($t)
        {
			$t->string('granite_jo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_granites', function ($t)
        {
            $t->dropColumn('granite_jo');
        });
    }

}
