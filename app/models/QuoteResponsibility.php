<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 7/22/17
 * Time: 1:13 PM
 */

class QuoteResponsibility extends Eloquent
{
    protected $guarded = [];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function responsibility()
    {
        return $this->belongsTo(Responsibility::class);
    }
}