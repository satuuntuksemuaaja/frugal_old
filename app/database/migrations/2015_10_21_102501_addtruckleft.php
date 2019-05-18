<?php

use Illuminate\Database\Migrations\Migration;

class Addtruckleft extends Migration
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
            $t->boolean('truck_left');
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
            $t->dropColumn('truck_left');
        });
    }

}
