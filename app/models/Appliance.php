<?php
class Appliance extends Eloquent
{

  public function designation()
  {
    return $this->belongsTo('Designation');
  }


}