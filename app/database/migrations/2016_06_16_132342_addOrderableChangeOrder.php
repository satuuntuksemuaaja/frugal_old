<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderableChangeOrder extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('change_order_details', function ($t)
        {
            $t->boolean('orderable');
            $t->datetime('ordered_on');
            $t->integer('ordered_by');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('change_order_details', function ($t)
        {
            $t->dropColumn('orderable');
            $t->dropColumn('ordered_on');
            $t->dropColumn('ordered_by');

        });
    }

}
