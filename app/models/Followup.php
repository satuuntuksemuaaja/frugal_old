<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 9/26/15
 * Time: 12:04 PM
 */
class Followup extends \Eloquent
{

    public function lead()
    {
        return $this->belongsTo('Lead');
    }

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function status()
    {
        return $this->belongsTo('Status');
    }

}