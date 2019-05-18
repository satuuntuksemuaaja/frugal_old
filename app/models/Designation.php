<?php
class Designation extends Eloquent
{
  public function users()
  {
    return $this->hasMany('User');
  }


}