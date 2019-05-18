<?php

use Illuminate\Database\Migrations\Migration;

class AddExpiresBefore extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status_expirations', function ($t)
        {
            $t->integer('expires_before');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_expirations', function ($t)
        {
            $t->dropColumn('expires_before');
        });
    }

}
