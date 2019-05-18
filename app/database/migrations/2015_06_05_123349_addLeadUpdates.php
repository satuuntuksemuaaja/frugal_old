<?php

use Illuminate\Database\Migrations\Migration;

class AddLeadUpdates extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_updates', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('lead_id');
            $t->integer('old_status');
            $t->integer('status');
            $t->integer('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lead_updates');
    }

}
