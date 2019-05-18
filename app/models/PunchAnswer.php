<?php
class PunchAnswer extends Eloquent
{
  public function punch()
  {
    return $this->belongsTo('Punch');
  }

  public function job()
  {
    return $this->belongsTo('Job');
  }
}