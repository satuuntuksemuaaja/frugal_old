<?php

use Illuminate\Database\Migrations\Migration;

class AddBuildLoadedFlags extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function ($t)
        {
            $t->boolean('built');
            $t->boolean('loaded');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function ($t)
        {
            $t->dropColumn('built');
            $t->dropColumn('loaded');
        });
    }

}
