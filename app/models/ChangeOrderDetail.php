<?php

class ChangeOrderDetail extends Eloquent
{
    public function getDates()
    {
        return ['created_at', 'updated_at', 'ordered_on'];
    }

    public function order()
    {
        return $this->belongsTo('ChangeOrder');
    }

    public function user()
    {
        return $this->belongsTo('User');
    }
    
    public function by()
    {
        return $this->belongsTo('User', 'ordered_by');
    }
}