<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10/10/16
 * Time: 5:35 PM
 */
class AuthorizationItem extends Eloquent
{
    public function authorization()
    {
        return $this->belongsTo('Authorization');
    }


}