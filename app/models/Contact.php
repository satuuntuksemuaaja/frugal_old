<?php
class Contact extends Eloquent
{
  public function customer()
  {
    return $this->belongsTo('Customer');
  }


}