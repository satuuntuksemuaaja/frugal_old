<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10/10/16
 * Time: 8:12 PM
 */
class Addon extends Eloquent
{
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
}