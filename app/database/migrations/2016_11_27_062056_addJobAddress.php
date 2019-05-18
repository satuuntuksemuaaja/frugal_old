<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJobAddress extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function ($t)
        {
            $t->string('job_address');
            $t->string('job_city');
            $t->string('job_state');
            $t->string('job_zip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function ($t)
        {
            $t->dropColumn('job_address');
            $t->dropColumn('job_city');
            $t->dropColumn('job_state');
            $t->dropColumn('job_zip');
        });
    }

}
