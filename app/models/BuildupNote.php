<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10/21/15
 * Time: 11:52 AM
 */
class BuildupNote extends Eloquent
{
    public function job()
    {
        return $this->belongTo('Job');
    }

    public function user()
    {
        return $this->belongsTo('User');
    }

}