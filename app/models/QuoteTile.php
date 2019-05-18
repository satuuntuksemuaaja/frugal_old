<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 7/23/17
 * Time: 12:37 PM
 */

class QuoteTile extends Eloquent
{
    protected $guarded = [];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}