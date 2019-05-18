<?php

use Illuminate\Database\Migrations\Migration;

class AddFollowupLock extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statuses', function ($t)
        {
            $t->boolean('followup_lock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statuses', function ($t)
        {
            $t->dropColumn('followup_lock');
        });
    }

}
