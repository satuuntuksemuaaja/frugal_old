<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaidAndReasontoFFT extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ffts', function ($t)
        {
            $t->boolean('paid');
            $t->text('paid_reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ffts', function ($t)
        {
            $t->dropColumn('paid');
            $t->dropColumn('paid_reason');
        });
    }

}
