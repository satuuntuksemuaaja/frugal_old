<?php

class ChangeOrder extends Eloquent
{
    public function getDates()
    {
        return ['created_at', 'updated_at', 'signed_on', 'sent_on'];
    }

    public function job()
    {
        return $this->belongsTo('Job');
    }

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function items()
    {
        return $this->hasMany('ChangeOrderDetail', 'change_order_id');
    }
    /*
     * Is everything ordered?
     * 
     */
    public function getOrderedAttribute()
    {
        $allOrdered = true;
        foreach ($this->items AS $item)
        {
            if ($item->orderable && !$item->ordered_by)
                $allOrdered = false;
        }
        return $allOrdered;
    }
}