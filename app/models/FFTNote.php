<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/18/17
 * Time: 1:12 PM
 */

class FFTNote extends Eloquent
{
    public $table = "fft_notes";

    public function fft()
    {
        return $this->belongsTo(FFT::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}