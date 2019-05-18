<?php

class Status extends Eloquent
{
    public $table = "statuses";

    public function expirations()
    {
        return $this->hasMany('Expiration');
    }

    static public function getStatusById($id)
    {
        return self::find($id)->name;
    }
}