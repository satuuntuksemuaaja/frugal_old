<?php

/**
 * @property int    reference
 * @property string instanceof
 * @property mixed  job_id
 * @property  reference
 */
class JobItem extends Eloquent
{
    protected $guarded = ['id'];
    public function job()
    {
        return $this->belongsTo('Job');
    }

    public function poitem()
    {
        return $this->belongsTo('PoItem', 'po_item_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

}