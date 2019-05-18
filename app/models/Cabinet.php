<?php
class Cabinet extends Eloquent
{
  public function vendor()
  {
    return $this->belongsTo('Vendor');
  }


}