<?php

class Customer extends Eloquent
{
    public function contacts()
    {
        return $this->hasMany('Contact');
    }

    public function notes()
    {
        return $this->hasMany('Note');
    }

    public function tasks()
    {
        return $this->hasMany('Task');
    }

    public function leads()
    {
        return $this->hasMany('Lead');
    }

    public function quotes()
    {
        return $this->hasManyThrough('Quote', 'Lead');
    }


}