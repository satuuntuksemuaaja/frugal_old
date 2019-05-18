<?php

use Illuminate\Database\Migrations\Migration;

class AddWarningToLead extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function($t)
        {
           $t->string('warning');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function($t)
        {
            $t->dropColumn('warning');
        });
    }

}
