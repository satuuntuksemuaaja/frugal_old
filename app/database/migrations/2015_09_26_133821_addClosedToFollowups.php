<?php

use Illuminate\Database\Migrations\Migration;

class AddClosedToFollowups extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('followups', function ($t)
        {
			$t->boolean('closed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('followups', function ($t)
        {
            $t->dropColumn('closed');
        });
    }

}
