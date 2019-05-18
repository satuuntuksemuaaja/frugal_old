<?php

use Illuminate\Database\Migrations\Migration;

class AddWarningIconForExpirations extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status_expirations', function ($t)
        {
            $t->string('warning',1);
            $t->string('type');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_expirations', function ($t)
        {
            $t->dropColumn('warning');
            $t->dropColumn('type');
        });
    }

}
