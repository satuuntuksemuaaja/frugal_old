<?php

use Illuminate\Database\Migrations\Migration;

class AddFollowupIdToNotifications extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function ($t)
        {
            $t->integer('followup_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function ($t)
        {
            $t->dropColumn('followup_id');
        });
    }

}
