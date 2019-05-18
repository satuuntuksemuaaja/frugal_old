<?php

use Illuminate\Database\Migrations\Migration;

class AddBuildUpChecklist extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function ($t)
		{
            $t->boolean('on_checklist');
        });

        Schema::create('checklists', function($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->text('question');
            $t->string('category');

        });
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function ($t)
        {
            $t->dropColumn('on_checklist');
        });

        Schema::drop('checklists');
    }

}
