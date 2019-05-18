<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJobNotesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_notes', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('user_id');
            $t->integer('job_id');
            $t->text('note');
        });

        foreach (Job::all() as $job)
        {
            if ($job->notes)
            {
                $note = new JobNote();
                $note->job_id = $job->id;
                $note->user_id = 0;
                $note->note = $job->notes;
                $note->save();
            }
        }

        // Seed on Migration

        Schema::table('jobs', function ($t)
        {
            $t->dropColumn('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('job_notes');
        Schema::table('jobs', function ($t)
        {
            $t->text('notes');
        });
    }

}
