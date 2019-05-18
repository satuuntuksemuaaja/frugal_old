<?php
class Punch extends Eloquent
{

  public function designation()
  {
    return $this->belongsTo('Designation');
  }

  public function answers()
  {
    return $this->hasMany('PunchAnswer');
  }


}