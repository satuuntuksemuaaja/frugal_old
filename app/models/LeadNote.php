<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 6/3/15
 * Time: 1:33 PM
 */

class LeadNote extends Eloquent {

    public function lead()
    {
        return $this->belongsTo('Lead');
    }

    public function user()
    {
        return $this->belongsTo('User');
    }


}