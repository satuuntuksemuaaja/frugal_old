<?php

use Illuminate\Database\Migrations\Migration;

class AddSignoff extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ffts', function ($t)
        {
            $t->text('signoff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ffts', function ($t)
        {
            $t->dropColumn('signoff');
        });
    }

}
