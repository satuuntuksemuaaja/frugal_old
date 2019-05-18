<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10/10/16
 * Time: 8:13 PM
 */
class QuoteAddon extends Eloquent
{

    public function addon()
    {
        return $this->belongsTo('Addon');
    }

    public function quote()
    {
        return $this->belongsTo('Quote');
    }

}