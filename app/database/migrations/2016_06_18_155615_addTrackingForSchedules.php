<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrackingForSchedules extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('showrooms', function ($t)
        {
            $t->integer('user_id');
        });

        Schema::table('closings', function($t)
        {
           $t->integer('user_id'); 
        });
        
        Schema::table('measures', function($t)
        {
           $t->integer('user_id'); 
        });
        
        Schema::table('leads', function($t)
        {
           $t->integer('last_status_by'); 
        });
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('showrooms', function ($t)
        {
            $t->dropColumn('user_id');
        });

        Schema::table('closings', function($t)
        {
            $t->dropColumn('user_id');
        });

        Schema::table('measures', function($t)
        {
            $t->dropColumn('user_id');
        });

        Schema::table('leads', function($t)
        {
            $t->dropColumn('last_status_by');
        });
    }

}
