<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFFTNoteThread extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('fft_notes', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->integer('fft_id');
           $t->integer('user_id');
           $t->string('note', 2048);
        });

		foreach (FFT::all() as $fft)
        {
            Log::info("Migrating Notes for $fft->id");
            $notes = explode("\n", $fft->notes);
            foreach ($notes as $note)
            {
                if (!trim($note)) continue;
                $n = new FFTNote();
                $n->fft_id = $fft->id;
                $n->note = $note;
                $n->user_id = 0;
                $n->save();
            }
        }

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('fft_notes');
	}

}
