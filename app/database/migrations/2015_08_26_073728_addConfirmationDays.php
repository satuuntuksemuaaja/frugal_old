<?php

use Illuminate\Database\Migrations\Migration;

class AddConfirmationDays extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendors', function ($t)
        {
            $t->integer('confirmation_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendors', function ($t)
        {
            $t->dropColumn('confirmation_days');
        });
    }

}
