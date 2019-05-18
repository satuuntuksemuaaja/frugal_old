<?php
class Shop extends Eloquent
{
    protected $guarded = [];

    public function cabinets()
    {
        return $this->hasMany(ShopCabinet::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function jobitem()
    {
        return $this->belongsTo(JobItem::class, 'job_item_id');
    }
}
