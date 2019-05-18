<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFAQPage extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faqs', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->string('question');
            $t->text('answer');
            $t->string('image');
            $t->string('figure');
            $t->string('type');
            $t->boolean('active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('faqs');
    }

}
