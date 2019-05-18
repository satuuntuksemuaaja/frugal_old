<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPunchReminder extends Migration
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
            $t->boolean('punch_reminder_emailed');
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
            $t->dropColumn('punch_reminder_emailed');
        });
    }

}
