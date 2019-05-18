<?php

class Lead extends Eloquent
{

    public function getDates()
    {
        return ['created_at', 'updated_at', 'last_note'];
    }

    public function customer()
    {
        return $this->belongsTo('Customer');
    }

    public function status()
    {
        return $this->belongsTo('Status');
    }

    public function showroom()
    {
        return $this->hasOne('Showroom');
    }

    public function closing()
    {
        return $this->hasOne('Closing');
    }

    public function measure()
    {
        return $this->hasOne('Measure');
    }

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function quotes()
    {
        return $this->hasMany('Quote');
    }

    public function source()
    {
        return $this->belongsTo('LeadSource');
    }

    public function notes()
    {
        return $this->hasMany('LeadNote');
    }

    public function followups()
    {
        return $this->hasMany('Followup');
    }

   public function laststatus()
   {
       return $this->belongsTo('User', 'last_status_by');
   }



}