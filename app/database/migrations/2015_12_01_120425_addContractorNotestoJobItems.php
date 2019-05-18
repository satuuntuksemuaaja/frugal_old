<?php

use Illuminate\Database\Migrations\Migration;

class AddContractorNotestoJobItems extends Migration
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
			$t->text('contractor_notes');
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
            $t->dropColumn('contractor_notes');
        });
    }

}
