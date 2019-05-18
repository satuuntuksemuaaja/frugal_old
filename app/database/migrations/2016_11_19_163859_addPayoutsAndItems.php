<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPayoutsAndItems extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payouts', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('user_id');
            $t->integer('job_id');
            $t->boolean('paid');
            $t->boolean('archived');
            $t->boolean('approved');
            $t->timestamp('paid_on');
            $t->text('notes');
            $t->string('check');
            $t->string('invoice');
            $t->double('total');
        });
        Schema::create('payout_items', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('payout_id');
            $t->string('item');
            $t->double('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payout_items');
        Schema::drop('payouts');
    }

}
