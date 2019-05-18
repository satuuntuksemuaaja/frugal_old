<?php

use Illuminate\Database\Migrations\Migration;

class AddCustomerReview extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function ($t)
        {
            $t->boolean('reviewed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function ($t)
        {
            $t->dropColumn('reviewed');
        });
    }

}
