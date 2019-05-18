<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/17/17
 * Time: 9:40 AM
 */

class Promotion extends Eloquent
{

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

}