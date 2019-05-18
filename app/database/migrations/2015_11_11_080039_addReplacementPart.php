<?php

use Illuminate\Database\Migrations\Migration;

class AddReplacementPart extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_items', function ($t)
        {
            $t->boolean('replacement');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_items', function ($t)
        {
            $t->dropColumn('replacement');
        });
    }

}
