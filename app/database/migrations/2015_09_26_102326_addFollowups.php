<?php

use Illuminate\Database\Migrations\Migration;

class AddFollowups extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followups', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('stage');
            $t->integer('lead_id');
            $t->integer('status_id');
            $t->integer('user_id');
            $t->text('comments');

        });

        Schema::table('statuses', function($t)
        {
           $t->boolean('followup_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('followups');
        Schema::table('statuses', function($t)
        {
            $t->dropColumn('followup_status');
        });
    }

}
