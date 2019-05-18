<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 6/5/15
 * Time: 12:36 PM
 */

class LeadUpdate extends Eloquent {

    public function lead()
    {
        return $this->belongsTo('Lead');
    }

    public function user()
    {
        return $this->belongsTo('User');
    }
    public function newstatus()
    {
        return $this->belongsTo('Status', 'status');
    }
}