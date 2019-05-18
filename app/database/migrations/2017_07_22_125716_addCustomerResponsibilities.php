<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerResponsibilities extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responsibilities', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->string('name');
            $t->boolean('active')->default(true);
        });

        Schema::create('quote_responsibilities', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->integer('quote_id');
            $t->integer('responsibility_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('responsibilities');
        Schema::drop('quote_responsibilities');

    }

}
