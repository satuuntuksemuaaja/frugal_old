<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10/10/16
 * Time: 5:34 PM
 */
class Authorization extends Eloquent
{
    public function job()
    {
        return $this->belongsTo('Job');
    }

    public function items()
    {
        return $this->hasMany('AuthorizationItem');
    }


}