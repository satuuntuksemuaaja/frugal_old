<?php

class PoItem extends Eloquent
{
    public function po()
    {
        return $this->belongsTo('Po');
    }

    public function jobitem()
    {
        return $this->belongsTo('JobItem');
    }

    public function createdby()
    {
        return $this->belongsTo('User');
    }

    public function receivedby()
    {
        return $this->belongsTo('User', 'received_by');
    }

    public function fft()
    {
        return $this->belongsTo(JobItem::class, 'fft_id');
    }

    public function service()
    {
        return $this->belongsTo(JobItem::class, 'service_id');
    }

    public function warranty()
    {
        return $this->belongsTo(JobItem::class, 'warranty_id');
    }
}