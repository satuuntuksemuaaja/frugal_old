<?php
class Snapshot extends Eloquent
{
  public function quote()
  {
    return $this->belongsTo('Quote');
  }


}