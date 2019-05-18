<?php

class Measure extends Eloquent
{
    public function getDates()
    {
        return ['created_at', 'updated_at', 'scheduled'];
    }

    public function user()
    {
        return $this->belongsTo('User', 'measurer_id');
    }

    public function lead()
    {
        return $this->belongsTo('Lead');
    }

    public function lastuser()
    {
        return $this->belongsTo('User');
    }

}