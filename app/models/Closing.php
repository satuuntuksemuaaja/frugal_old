<?php

class Closing extends Eloquent
{

    public function getDates()
    {
        return ['created_at', 'updated_at', 'scheduled'];
    }

    public function lead()
    {
        return $this->belongsTo('Lead');
    }

    public function user()
    {
        return $this->belongsTo('User');
        
    }

}