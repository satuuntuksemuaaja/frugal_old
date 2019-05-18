<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 7/23/17
 * Time: 9:37 AM
 */

class QuoteAppliance extends Eloquent
{
    protected $guarded = [];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function appliance()
    {
        return $this->belongsTo(Appliance::class);
    }
}