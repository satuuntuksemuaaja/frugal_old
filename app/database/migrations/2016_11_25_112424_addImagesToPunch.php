<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImagesToPunch extends Migration
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
            $t->string('image1');
            $t->string('image2');
            $t->string('image3');
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
            $t->dropColumn('image1');
            $t->dropColumn('image2');
            $t->dropColumn('image3');
        });
    }

}
