<?php
class Hardware extends Eloquent
{
  public function vendor()
  {
    return $this->belongsTo('Vendor');
  }



}