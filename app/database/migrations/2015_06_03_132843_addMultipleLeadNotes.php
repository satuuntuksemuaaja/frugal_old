<?php

use Illuminate\Database\Migrations\Migration;

class AddMultipleLeadNotes extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_notes', function ($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('lead_id');
            $t->text('note');
            $t->integer('user_id');
        });

        Schema::table('leads', function ($t)
        {
            $t->dropColumn('notes');
            $t->datetime('last_note');
        });
        Eloquent::unguard();
        Setting::create(['name' => 'lead_red']);
        Setting::create(['name' => 'lead_followup']);
        Setting::create(['name' => 'lead_followup_content']);
        Setting::create(['name' => 'lead_warning']);
        Setting::create(['name' => 'lead_warning_content']);
   }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::Drop('lead_notes');
        Schema::table('leads', function ($t)
        {
            $t->text('notes');
            $t->dropColumn('last_note');
        });
    }

}
