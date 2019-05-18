<?php

use Illuminate\Database\Migrations\Migration;

class AddBuildupNotes extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buildup_notes', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('user_id');
            $t->integer('job_id');
            $t->text('note');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('buildup_notes');
    }

}
